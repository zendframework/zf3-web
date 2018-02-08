---
layout: post
title: Expressive 3 Alpha 3
date: 2018-02-08T10:50:00-05:00
author: Matthew Weier O'Phinney
url_author: https://mwop.net
permalink: /blog/2018-02-08-expressive-3-alpha3.html
categories:
- blog
- expressive
- psr-15

---

Today, we pushed the final changes and fixes that culminated in the
**[Expressive Installer and Skeleton 3.0.0alpha3
release](https://github.com/zendframework/zend-expressive-skeleton/releases/3.0.0alpha3)**!

The alpha releases have a ton of great features; keep reading to find out more!

> ### Alpha 1 and Alpha 2
>
> We released 3.0.0alpha1 on Tuesday, 6 February 2018, and 3.0.0alpha2 on
> Wednesday, 7 February 2018. While they were usable, there were a few issues we
> discovered that we felt should be addressed before a public announcement.
> 3.0.0alpha3 represents a stable, testable release.

## Installation

Currently, we do not have a completed migration path for 3.0; this is our work
in the coming weeks. Additionally, there may yet be changes coming as we get
_your_ feedback. As such, we recommend:

- Install a fresh project.
- Do not use the alpha release(s) in production!

To create a new project based on 3.0.0alpha3, use
[Composer](https://getcomposer.org):

```bash
$ composer create-project "zendframework/zend-expressive-skeleton:3.0.0alpha3"
```

This will install the skeleton, and then start prompting you for specific
components you want to install, including choice of
[PSR-11](https://www.php-fig.org/psr/psr-11/) container, choice of router,
choice of template engine, and choice of error handler. Generally speaking, we
recommend the default values, except in the case of the template engine (which
defaults to none; choose the engine you're most comfortable with).

Once your selections are made, the skeleton will install dependencies; when it
is complete, you can enter the newly created directory to begin development:

```bash
$ cd zend-expressive-skeleton
```

> ### Alternate directory
> 
> You can specify an alternate directory when calling `composer create-project`;
> when you do, you can also specify the specific version separate from the root
> package:
>
> ```bash
> $ composer create-project zendframework/zend-expressive-skeleton expressive 3.0.0alpha3
> ```

## Creating middleware

Version 3 of Expressive will work with
[PSR-15](https://www.php-fig.org/psr/psr-15) (HTTP server request handlers)
middleware and request handlers **only**. You will be writing these to create
your application.

> ### Other supported types
>
> Expressive 3 also supports other types of middleware definitions, though they
> are not recommended:
>
> - Callable middleware using the same signature as PSR-15. These can be piped
>   and routed to directly.
> - Callable double-pass middleware; these must be decorated using the
>   `Zend\Stratigility\doublePassMiddleware()` utility class &mdash; which also
>   requires a PSR-7 response prototype.

The skeleton project now ships with [zend-expressive-tooling](https://github.com/zendframework/zend-expressive-tooling)
by default, and maps its `expressive` command as a composer command:

```bash
$ composer expressive help
```

To create your first middleware:

```bash
$ composer expressive middleware:create "App\XClacksOverheadMiddleware"
```

This will create the class `App\XClacksOverheadMiddleware`, and tell you where
it has been created. You can then edit it:

```php
<?php
namespace App;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class XClacksOverheadMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $response = $handler->handle($request);
        return $response->withHeader('X-ClacksOverhead', 'GNU Terry Pratchett');
    }
}
```

Once your middleware is created, register it in the container as an invokable,
via the `config/autoload/dependencies.global.php` file:

```php
'dependencies' => [
    'invokables' => [
        App\XClacksOverheadMiddleware::class => App\XClacksOverheadMiddleware::class,
    ],
],
```

Finally, register it in your `config/pipeline.php` file:

```php
// likely an early statement, before routing
$app->pipe(App\XClacksOverheadMiddleware::class);
```

You've just created your first middleware!

## Creating handlers

PSR-15 defines two interfaces. In the previous section, we demonstrated
implementing the `MiddlewareInterface`. That interface references another, the
`RequestHandlerInterface`.

Internally, we provide one that maintains the middleware pipeline and the state
within the pipeline such that calling `handle()` advances to the next
middleware.

However, there's another place handlers are of interest: for routes.

Most often, when you create a route, the class you write to handle the route
will generate a response itself, and never need to delegate to another handler.
As such, you can write handlers instead!

Like middleware, the tooling provides a command for creating handlers:

```bash
$ composer expressive handler:create "App\Handler\HelloWorldHandler"
```

This will create a `RequestHandlerInterface` implementation using the given
name, and then tell you where on the filesystem it created it.

For this example, we'll assume you're using zend-diactoros (as it is used in the
Expressive skeleton by default), and we'll create a handler that generates a
`Zend\Diactoros\Response\HtmlResponse`. Open the file, and edit the contents to
look like the following:

```php
<?php

namespace App\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;

class HelloWorldHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        return new HtmlResponse('<h1>Hello, world!</h1>');
    }
}
```

Like the `XClacksOverhead` middleware, We'll register this with the container as
an invokable, via the file `config/autoload/dependencies.global.php`:

```php
'dependencies' => [
    'invokables' => [
        App\Handler\HelloWorldHandler::class => App\Handler\HelloWorldHandler::class,
    ],
],
```

Finally, we'll route to it via your `config/routes.php` file:

```php
$app->get('/hello', App\Handler\HelloWorldHandler::class, 'hello');
```

You've just created your first handler!

> ### Handlers and route-specific pipelines
>
> If you have used Expressive before, you may recall that the various routing
> methods allowed middleware and middleware pipelines previously. This is still
> true! The only difference with version 3 is that we _also_ allow request
> handlers.
>
> In fact, you can add handlers into your middleware pipelines as well! If
> we wanted the `XClacksOverheadMiddleware` to only be in that specific route,
> we could write it as follows:
>
> ```php
> $app->get('/hello', [
>     App\XClacksOverheadMiddleware::class,
>     App\Handler\HelloWorldHandler::class,
> ], 'hello')
> ```
>
> The only caveat is that handlers _always return a response_, which means they
> should always be the _last_ item in a pipeline.

## Test it out!

You can use the following command to fire up the PHP built-in web server:

```bash
$ composer serve
```

The command works on Windows and macOS; for Linux users, it will work as long as
you have PHP 7.1.14 and later or 7.2.2 and later installed. For those on earlier
versions, use the following:

```php
$ php -S 0.0.0.0:8080 -t public/ public/index.php
```

Once you have, try hitting your new route: http://localhost:8080/hello

You should get your HTML content as defined above! If you introspect the request
in your browser (or using a CLI tool such as cURL or HTTPie), you'll also see a
`X-Clacks-Overhead` header from the middleware you created!

## Roadmap

Hitting our first alpha releases is a huge milestone, and the culmination of
many months of development. We're very excited about the results. In both
Stratigility (our middleware foundation library) and Expressive, we have 
drastically reduced the amount of code, while providing essentially the same
feature set (and, in many cases, _expanding_ that feature set!).

In the coming weeks, we'll be developing a final version 2 minor release, 2.2.0,
as well as working on documentation and migration tooling. The main goal of the
2.2 release will be to mark deprecated features, provide forward compatible
alternatives, and provide tooling to help you migrate to those alternatives.

We also have a few planned features for Expressive 3 to complete. We're working
on changes to the zend-component-installer to allow whitelisting packages with
configuration providers, so that users will not need to be prompted during
initial installation to inject configuration for packages we already know about.
We also plan to develop tooling for creating and registering factories based on
a given class, and updating the `handler:create` and `middleware:create`
factories to generate and register factories as well. Also, recently we released
version 3 of the zend-di package, and we're considering integrating it by
default when zend-servicemanager is configured, to provide auto-wiring of
dependencies.

This may sound like quite a bit, but much of it is already in progress, and our
plan is to have a stable release by no later than 15 March 2018.

In the meantime: install and test 3.0.0alpha3! Kick its tires, and let us know
what works, and, more importantly, what _doesn't_ work, so we can provide you a
stable, exciting 3.0.0 release!
