---
layout: post
title: Expressive 3!
date: 2018-03-16T10:15:00-05:00
author: Matthew Weier O'Phinney
url_author: https://mwop.net
permalink: /blog/2018-03-16-expressive-3.html
categories:
- blog
- expressive
- psr-15
- psr-11
- psr-7

---

Yesterday, we tagged and released Expressive 3!

**Expressive 3 provides a middleware microframework.**

Create a new Expressive application using [Composer](https://getcomposer.org):

```bash
$ composer create-project zendframework/zend-expressive-skeleton
```

The installer will prompt you for your choice of:

- Initial application architecture (minimal, flat, modular)
- Which dependency injection container you would like to use.
- Which routing library you would like to use.
- Which templating library you would like to use, if any.
- Which error handling library you would like to use, if any.

From there, it creates a new project for you, and allows you to get started
developing immediately.

You can read more [in our quick start](https://docs.zendframework.com/zend-expressive/v3/getting-started/quick-start/),
and may want to [check out our command line tooling](https://docs.zendframework.com/zend-expressive/v3/reference/cli-tooling/#expressive-command-line-tool)
to see what we provide to make development even faster for you!

## What are the features?

Expressive 3 embraces modern PHP, and **requires PHP 7.1 or higher**. Strong
type-hinting, including return type hints, make both our job and _your_ job
easier and more predictable. The ability to use all modern PHP features helps us
deliver a solid base for _your_ application.

Expressive 3 provides **full support for the [PSR-15 (Middleware and Request
Handlers) standard](https://www.php-fig.org/psr/psr-15/)**. We believe strongly
in supporting standards, to the extent that this release also [drops direct
support for the "double-pass" middleware style](https://docs.zendframework.com/zend-expressive/v3/getting-started/features/#double-pass-middleware)
we have supported since version 1.<sup id="target-0">[0](#footnote-0)</sup>

Expressive 3 massively refactors its internals as well. In fact, the majority of
the code in the zend-expressive package was _removed_, moved to other existing
packages where it had a better semantic affiliation<sup id="target-1">[1](#footnote-1)</sup>,
or extracted to new packages<sup id="target-2">[2](#footnote-2)</sup>. This base
package now mainly handles coordinating collaborators and providing a
user-friendly interface to creating your application pipeline and routes.<sup id="target-3">[3](#footnote-3)</sup>

Expressive 3 provides more command line tooling and tooling improvements in
order to make developing your application easier. We added a command for
creating factories for existing classes (`factory:create`).<sup id="target-4">[4](#footnote-4)</sup>
The `middleware:create` command now creates a factory for the
middleware generated. We added support for creating request handlers<sup id="target-5">[5](#footnote-5)</sup>,
complete with factory generation and registration, as well as template support.<sup id="target-6">[6](#footnote-6)</sup>

Finally, we recognize that Expressive has changed massively between versions 1
and 3, while simultaneously keeping its primary API stable and unchanged.
However, to help users find the information they need for the version they run,
we have rolled out versioned documentation, with each version providing only
information specific to its release cycle:

- [Version 1](https://docs.zendframework.com/zend-expressive/v1/)
- [Version 2](https://docs.zendframework.com/zend-expressive/v2/)
- [Version 3](https://docs.zendframework.com/zend-expressive/v3/getting-started/features/)

The most recent version will always be present in the primary navigation, with
links to other versions present as well.

## New components!

We have several new components that provide features for Expressive &mdash; or
any PSR-15 framework you may be using! These include:

- [zend-expressive-session](https://docs.zendframework.com/zend-expressive-session/),
  which provides session abstraction and middleware. We also provide a single
  adapter, presently, that utilizes PHP's session extension, 
  [zend-expressive-session-ext](https://docs.zendframework.com/zend-expressive-session-ext/).

- [zend-expressive-flash](https://docs.zendframework.com/zend-expressive-flash/)
  provides flash message support, using zend-expressive-session.

- [zend-expressive-csrf](https://docs.zendframework.com/zend-expressive-csrf/)
  provides Cross Site Request Forgery protection, using zend-expressive-session
  and/or zend-expressive-flash.

- [zend-problem-details](https://docs.zendframework.com/zend-problem-details/)
  provides [Problem Details](https://tools.ietf.org/html/rfc7807) responses for
  your APIs, in both JSON and XML formats.

- [zend-expressive-hal](https://docs.zendframework.com/zend-expressive-hal/)
  provides tools for building [HAL](https://tools.ietf.org/html/draft-kelly-json-hal-08)
  response payloads for your API, in both JSON and XML formats.

We have a number of other packages in the works around authentication,
authorization, and data validation that we will be releasing in the coming weeks
and months; stay tuned for announcements!

## What about upgrading?

We have prepared a [migration document](https://docs.zendframework.com/zend-expressive/v3/reference/migration/)
that covers new features, removed features, and a list of all changes.

Additionally, we have provided [migration tooling](https://docs.zendframework.com/zend-expressive/v3/reference/migration/#upgrading)
to aid you in your migration from version 2 to version 3. _The tool will not
necessarily give you a fully running application_, but it _will_ take care of
the majority of the changes necessary to bump your application to version 3,
including setting up appropriate dependencies, and updating your bootstrapping
files to conform to the new skeleton application structure.

If you need assistance, you can find community help:

- in our [Slack](https://zendframework-slack.herokuapp.com)
- in our [forums](https://discourse.zendframework.com/c/questions/expressive/)

## What's next?

We have been working on a number of API-related modules for Expressive (and any
PSR-15 applications) since last summer, with a number of components already
completed, and others close to completion. We plan to finalize these in the next
few months.

## Thank You!

We extend a hearty thank you to everyone who tested the various pre-releases and
provided feedback. Additionally, we are singling out the following individuals
who provided significant contributions to the Expressive 3 project:

- [Enrico Zimuel](https://www.zimuel.it/) provided a ton of feedback and
  critique during the design phase, and was a driving force behind many of the
  API usability decisions.

- [Rob Allen](https://akrabat.com/) did a workshop at SunshinePHP, right as we
  dropped our initial alpha releases, and provided feedback and testing for much
  of our tooling additions.

- [Frank Brückner](http://www.froschdesignstudio.de) provided ongoing feedback
  and review of pull requests, primarily around documentation; he is also
  responsible for a forthcoming rewrite of our documentation theme to make it
  more responsive and mobile-friendly.

- [Daniel Gimenes](https://github.com/danizord) provided feedback and ideas as
  we refactored zend-stratigility; he is the one behind package-level utility
  functions such as `Zend\Stratigility\doublePassMiddleware()`,
  `Zend\Stratigility\path()`, and more.

- [Witold Wasiczko](https://github.com/snapshotpl) provided the majority of the
  rewrite of zend-stratigility for version 3. He can be celebrated for removing
  over half the code from that repository!

In addition to these people, I want to extend a personal thank you to the
following people:

- [Geert Eltink](https://xtreamwayz.com/) has helped maintain Expressive v2, and
  particularly the various routers and template engines, making them ready for
  v3 and testing continually. As a maintainer, I was able to rely on him to take
  care of merges as we finalized the releases, and was pleasantly surprised to
  wake up to new releases several times when he fixed critical issues in our
  alpha and RC releases.

- [Michał Bundyra](https://github.com/webimpress) provided a constant stream of
  pull requests related to quality assurance (including ongoing work on our phpcs
  extension!), as well as critical review of incoming patches. He spearheaded
  important work in the refactoring process, including changes to how we handle
  response prototypes, and critical fixes in our routers to address issues with
  how we detect allowed methods for path route matches. We synced each and every
  single day, often arguing, but always coming to consensus and plowing on.

If you get a chance, reach out to these contributors and thank them for the
release!

### Footnotes

- <span id="footnote-0">[0](#target-0)</span>: The Expressive ecosystem makes
  use of many other standards as well, including
  [PSR-7 HTTP Messages](https://www.php-fig.org/psr/psr-7/),
  [PSR-11 Container](https://www.php-fig.org/psr/psr-11/), and
  [PSR-13 HTTP Links](https://www.php-fig.org/psr/psr-13/).

- <span id="footnote-1">[1](#target-1)</span>: As an example, the routing,
  dispatch, and "implicit methods" middleware were all moved to the
  [zend-expressive-router](https://github.com/zendframework/zend-expressive-router)
  package, as they each work with the router and route results.

- <span id="footnote-2">[2](#target-2)</span>: Request generation, application
  dispatch, and response emission were all moved to a new package, 
  [zend-httphandlerrunner](https://github.com/zendframework/zend-httphandlerrunner).

- <span id="footnote-3">[3](#target-3)</span>: These refactors led to a net
  _removal_ of code across the board, vastly simplifying the internals. This
  will lead to ease of maintenance, greater stability, and, based on benchmarks
  we've been performing, 10% better performance and less system resource usage.

- <span id="footnote-4">[4](#target-4)</span>: `factory:create` uses PHP's
  Reflection API in order to determine what dependencies are in place in order to
  generate a factory class; it also _registers_ the class and factory with the
  container!

- <span id="footnote-5">[5](#target-5)</span>: In previous Expressive versions,
  we referred to "actions", which were any middleware that returned a response
  instead of delegating to another layer of the application. PSR-15 calls such
  classes _request handlers_. Our tooling provides an `action:create` command,
  however, for those who prefer the "action" verbiage.

- <span id="footnote-6">[6](#target-6)</span>: The command creates a template
  named after the handler created; it uses the root namespace of the class to
  determine where to put it in the filesystem. Additionally, it alters the
  generated request handler to render the template into a zend-diactoros
  `HtmlResponse`!
