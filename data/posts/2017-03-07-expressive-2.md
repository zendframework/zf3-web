---
layout: post
title: Announcing Expressive 2.0
date: 2017-03-07T10:00:00-06:00
author: Matthew Weier O'Phinney
url_author: https://mwop.net/
permalink: /blog/2017-03-07-expressive-2.html
categories:
- blog
- expressive
- release
- psr-11
- psr-15

---

Today we're excited to announce Expressive 2.0!

What has changed since 1.0 was released last year?
 
The short version: we've been providing changes that standardize, simplify, and
streamline application development in Expressive.

Specifically:

- **[PSR-11](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-11-container.md)
  (ContainerInterface) support.** Expressive is now a PSR-11 consumer, and will
  work with any container compatible with that specification. As
  [container-interop 1.2.0](https://github.com/container-interop/container-interop/releases/tag/1.2.0)
  now extends the PSR-11 interfaces, any container-interop-compatible containers
  are now automatically PSR-11 compatible as well, and thus work with
  Expressive!

- **[http-interop/http-middleware](https://github.com/http-interop/http-middleware)
  support.** http-middleware is the working group for the proposed PSR-15
  specification, covering server-side middleware. We feel the specification is
  solidifying sufficiently to adopt and support it. In fact, while we continue
  to accept existing "double-pass" middleware, our engine internally only
  supports http-middleware!

- **Programmatic pipelines.** Matthew [blogged about programmatic pipelines last
  year](https://mwop.net/blog/2016-05-16-programmatic-expressive.html), and the
  response to that was very positive; many developers indicated they found the
  technique far more approachable, particularly when first learning Expressive.
  As such, the new skeleton application now creates programmatic pipelines, and
  we [provide a tool to allow you to convert existing, configuration-driven
  applications to the programmatic approach](https://docs.zendframework.com/zend-expressive/reference/cli-tooling/#migrate-to-programmatic-pipelines).

- **Simplified, improved error handling.** Error handling was unintuitive
  previously, due in part to architectural decisions in the underlying
  [zendframework/zend-stratigility](https://docs.zendframework.com/zend-stratigility/)
  package.  We have fixed those issues, allowing a [simpler, more flexible
  approach via standard middleware](https://docs.zendframework.com/zend-expressive/features/error-handling).

- **Modular applications.** [Mateusz Tymek](https://github.com/mtymek) created
  functionality last year for modularizing Expressive applications that we even
  documented in our cookbook. We have since expanded on those efforts to create
  a new component, [zendframework/zend-config-aggregator](https://github.com/zendframework/zend-config-aggregator),
  which is responsible for aggregating configuration from a variety of sources,
  and optionally caching the merged configuration for you. This functionality is
  now incorporated in our skeleton application by default, allowing you to
  consume or create re-usable middleware modules.

- **Extensible routing and dispatch middleware.** In version 1, the routing and
  dispatch middleware was incorporated directly within the
  `Zend\Expressive\Application` class. Each has now been extracted to its own
  class, allowing developers to extend them, or provide their own.

- **More development tooling.** We now incorporate [zfcampus/zf-development-mode](https://github.com/zfcampus/zf-development-mode)
  in the skeleton, allowing you to toggle development-specific configuration.
  Additionally, we have created [zendframework/zend-expressive-tooling](https://docs.zendframework.com/zend-expressive/reference/cli-tooling/),
  which provides a variety of tools for helping you migrate from Expressive 1 to
  Expressive 2, as well as for creating and managing middleware modules.

The above are the high-level features of the release; a lot of other changes
have gone into the release, which we cover thoroughly in our [migration
document](https://docs.zendframework.com/zend-expressive/reference/migration/to-v2/).

## Get the release!

### Existing users

If you are already using Expressive, you can update to the new version by
issuing the following statement:

```bash
$ composer require "zendframework/zend-expressive:^2.0"
```

Depending on other requirements you have in place, you may need to update the
following dependencies at the same time to the listed constraints; you may do so
by appending the necessary requirements to the above statement, within double
qoutes:

- `zendframework/zend-expressive-aurarouter:^2.0`
- `zendframework/zend-expressive-fastroute:^2.0`
- `zendframework/zend-expressive-zendrouter:^2.0.1`
- `zendframework/zend-expressive-helpers:^3.0.1`

Once you have upgraded, be sure to [read the migration
document](https://docs.zendframework.com/zend-expressive/reference/migration/to-v2/)
to see what other changes you may need to make to your application.

(In our tests, and those of our contributors, we found that the majority of
upgrades "just worked"; if you have difficulties, please [open an issue
detailing your specific problems](https://github.com/zendframework/zend-expressive/issues/new).)

### New users

New users can get started using [our skeleton application](https://docs.zendframework.com/zend-expressive/getting-started/skeleton/):

```bash
$ composer create-project zendframework/zend-expressive-skeleton
```

This will prompt you for a number of requirements; use the defaults if you are
unsure. Once done, enter the newly created project directory, and get started
developing!

## Thank you!

This release was largely a community-driven project. At this time, we extend our
deepest appreciation to the following contributors, who made huge contributions
in order to make the release happen (in alphabetical order, by surname):

- [Michał Bundyra](https://github.com/webimpress), who developed much of the
  tooling support, battle-tested migrations, and continuously provided QA
  improvements to the project.
- [Geert Eltink](https://xtreamwayz.com/), who contributed the bulk of the
  changes to the skeleton application, and who developed and maintains the
  installer.
- [Michael Moussa](https://github.com/michaelmoussa), who has done the majority
  of the day-to-day maintenance since last autumn, and who spear-headed many
  changes in the routing system and helpers.
- [Mateusz Tymek](https://github.com/mtymek), who contributed the modular
  architecture.

Many thanks to everyone who has contributed to the release, be it via feedback,
reporting issues, providing patches, or improving the documentation!

## Resources

We'll be following up in the coming days with more in-depth posts covering new
features and workflows, so keep an eye on this space.

In the meantime, here are some resources you can use immediately:

- [Expressive documentation](https://docs.zendframework.com/zend-expressive/)
- [Migration to 1.1](https://docs.zendframework.com/zend-expressive/reference/migration/to-v1-1/)
  (forwards-compatibility release).
- [Migration to 2.0](https://docs.zendframework.com/zend-expressive/reference/migration/to-v2/)
- [V2 project creation via the skeleton application](https://docs.zendframework.com/zend-expressive/getting-started/skeleton/)

> ## Save the date!
>
> Want to learn more about Expressive and Zend Framework? What better location
> than ZendCon 2017! ZendCon will be hosted 23-26 October 2017 in Las Vegas,
> Nevada, USA. [Visit the ZendCon website for more
> information](http://www.zendcon.com).

