---
layout: post
title: Async Expressive? Try Swoole!
date: 2018-03-21T15:55:00-05:00
author: Matthew Weier O'Phinney
url_author: https://mwop.net
permalink: /blog/2018-03-21-expressive-swoole.html
categories:
- async
- blog
- expressive
- psr-15
- psr-11
- psr-7
- swoole

---

When we were finalizing features for [Expressive 3](/blog/2018-03-16-expressive-3.html),
we had a number of users testing using asynchronous PHP web servers. As a
result, we made a number of changes in the last few iterations to ensure that
Expressive will work well under these paradigms.

Specifically, we made changes to how _response prototypes_ are injected into
services.

## Response prototypes?

What's the problem?

In an async system, one advantage is that you can bootstrap the application
once, and then respond to requests until the server is shutdown.

However, this can become problematic with services that compose a response
prototype in order to produce a response (e.g., authentication middleware that 
may need to produce an "unauthenticated" response; middleware that will produce
a "not found" response; middleware that will produce a "method not allowed"
response; etc.). We have standardized on providing response prototypes via
dependency injection, using a service named after the interface they implement:
`Psr\Http\Message\ResponseInterface`.

If a particular service accepts a response instance that's injected during
initial service creation, that same instance will be used for any subsequent
requests that require it. And that's where the issue comes in.

When running PHP under traditional conditions &mdash; php-fpm, the Apache SAPI,
etc. &mdash; all requests are isolated; the environment is both created and torn
down for each and every request. As such, passing an instance is perfectly safe;
there's very little chance, if any, that any other service will be working with
the same instance.

With an async server, however, the same instance will be used on each and every
request. Generally, manipulations of [PSR-7](https://www.php-fig.org/psr/psr-7/)
message instances will create _new instances_, as the interfaces they implement
are specified as _immutable_. Unfortunately, due to technical limitations of the
PHP language, we were unable to make the _body_ of response messages
_immutable_. This means that if one process writes to that body, then a
subsequent process &mdash; or even those executing in parallel! &mdash; will
receive the same changes. This can lead to, in the best case scenario,
duplicated content, and, in the worst, provide incorrect content or perform
information leaking!

To combat these situations, we modified the `Psr\Http\Message\ResponseInterface`
service we register with the dependency injection container: it now returns not
an _instance_ of the interface, but a _factory_ capable of producing an instance.
Services should compose this factory, and then call on it each time they need to
produce a response. This fixes the async problem, as it ensures a _new_ instance
is used each time, instead of the _same_ instance.

(Additionally, this change helps us prepare for the upcoming PSR-17, which
describes factories for PSR-7 artifacts; this solution will be compatible with
that specification once complete.)

## Why async?

If asynchronous systems operate so differently, why bother?

There's many reasons, but the one that generally gets the attention of
developers is **performance**.

We performed benchmarks of Expressive 2 and Expressive 3 under both Apache and
nginx, and found version 3 received around a 10% improvement.

We then tested using [Swoole](https://www.swoole.co.uk/). Swoole is a PHP
extension that provides built-in async, multi-threaded input/output (I/O)
modules; it's essentially the I/O aspects of node.js &mdash; which allow you to
create network servers and perform database and filesystem operations &mdash;
but for PHP.

A contributor, [Westin Shafer](https://github.com/wshafer), has written a [module
for Expressive 3 that provides an application wrapper for Swoole](https://github.com/wshafer/swoole-expressive)
that is exposed via a CLI command. We ran our same benchmarks against this, and
the results were astonishing: applications ran consistently **4 times faster**
under this asynchronous framework, and used fewer resources!

While performance is a great reason to explore async, there are other reasons as
well. For instance, if you do not need the return value of an I/O call (e.g., a
database transaction or cache operation), you can fire it off asynchronously,
and finish out the response without waiting for it. This can lead to reduced
waiting times for clients, further improving your performance.

We have had fun testing Swoole, and think it has tremendous possibilities when
it comes to creating microservices in PHP. The combination of Expressive and
Swoole is remarkably simple to setup and run, making it a killer combination!

> #### Notes on setting up Swoole
>
> The wshafer/swoole-expressive package requires a version 2 release of the
> Swoole extension.
>
> However, there's a slight bug in the PECL installer whereby it picks up the
> most recent release as the "latest", even if a version with greater stability
> exists.  As of the time of writing, version 1.10.2 of Swoole was released after
> version 2.1.1, causing it to be installed instead of the more 2.X version.
>
> You can force installation of a version by appending the version you want when
> invoking the `pecl` command:
>
> ```bash
> $ pecl install swoole-2.1.1
> ```
>
> The version must be fully qualified for it to install correctly; no partials
> (such as `swoole-2` or `swoole-2.1` will work.
