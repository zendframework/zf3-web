---
layout: post
title: Expressive 3.0.0RC1 is now ready!
date: 2018-02-27T16:25:00-05:00
author: Matthew Weier O'Phinney
url_author: https://mwop.net
permalink: /blog/2018-02-27-expressive-3-rc1.html
categories:
- blog
- expressive
- psr-15

---

We've been working diligently the past three weeks to finalize API changes and new
features for the Expressive 3.0 release, and are pleased to announce immediate
availability of our first release candidate, 3.0.0rc1!

> ### Why RC and not beta?
> 
> Why the jump from alpha to release candidate? Most of our planned features
> also contained API changes, whether these were namespace or class name changes,
> signature changes due to adopting type hints, or wholesale refactors. As a
> result, we held an extended alpha release cycle so that we could continue making
> API changes as we worked on new features. In the last three weeks, we've
> continued to release new alpha versions of components, which users were picking
> up as they updated; surprisingly, the majority of these continued to work due to
> efforts at backwards compatibility that we made along the way.
> 
> We feel at this point that we've identified and implemented all desired
> changes both in terms of API as well as features, and announced a feature freeze
> yesterday. This puts us in a status more analogous to release candidates than
> beta (where features could still be added).

In this post, we'll cover:

- [How to get started with RC1.](#getting-started-with-rc1)
- [How to migrate your existing 3.0.0alpha3 project to 3.0.0rc1.](#migrating-from-alpha3-to-rc1)
- [What changed between releases.](#changes-between-alpha3-and-rc1)
- [Ecosystem updates.](#ecosystem-updates)
- [Our roadmap to a stable release.](#roadmap)

## Getting started with RC1

To start a new project based on 3.0.0rc1, use
[Composer](https://getcomposer.org) to create a new project:

```bash
$ composer create-project "zendframework/zend-expressive-skeleton:3.0.0rc1"
```

If you want to install to a custom directory name, use the following instead:

```bash
$ composer create project zendframework/zend-expressive-skeleton {your directory} 3.0.0rc1
```

The installer will prompt you for a number of things:

- What style install (minimal, flat, or modular).
- Which DI container to use.
- Which router to use.
- Which template engine to use.
- Which error handler to use.

We recommend the defaults, except in the case of a template engine; in that
case, choose the one you're most familiar with.

Once the installer has gathered its information, it will begin installing
dependencies, enable development mode, and then let you know it's done!

You can create your first request handler to handle an incoming request using
our tooling:

```bash
$ composer expressive handler:create "App\Handler\HelloWorldHandler"
```

> ### Tooling
>
> Run the following command to find out what other commands we expose:
>
> ```bash
> $ composer expressive help
> ```
>
> You can also get help on individual commands:
>
> ```bash
> $ composer expressive help handler:create
> ```

This will create a new class and tell you where in the filesystem you'll find
it; it will also create a factory and tell you about that. If you have enabled a
template engine, it will also create a template file and tell you where it is.

To route to it, edit the file `config/routes.php` and add the following line
within the callback it defines:

```php
    $app->get('/hello/world', App\Handler\HelloWorldHandler::class);
```

Next, fire up the built-in web server:

```bash
$ composer serve
```

and navigate to http://localhost:8080/hello/world to see your handiwork!

You can also create middleware to add to your application pipeline, or within
route-specific pipelines. From here, you have the basic building blocks and
application structure to get started!

## Migrating from alpha3 and rc1

While a ton has changed between the 3.0.0alpha3 release and today, most
applications built on alpha3 should be able to continue working as they were
before, due to a number of backwards compatibility efforts we put into place to
aid migration both for alpha users, as well as v2 users.

However, we recommend making the following changes to your application as well,
to ensure it follows the structure that will be used in the final stable
release.

## composer.json

Whenever you see a constraint with the format `^X.Y.ZalphaW || ^X.Y`, remove the
`|| ^X.Y` part of the constraint. This ensures that a previous alpha version
cannot be installed if you use the `--prefer-lowest` flag when running `composer
update`. More stable versions will still be installed when they become
available.

## config/config.php

Ensure the following configuration providers are present:

```php
    \Zend\HttpHandlerRunner\ConfigProvider::class,
    \Zend\Expressive\ConfigProvider::class,
    \Zend\Expressive\Router\ConfigProvider::class,
```

Most likely, you will have been prompted to install these already anyways, but
double-check to be sure.

### config/pipeline.php

alpha3 refered to the following classes via import statements; they should be
updated per the following table:

| alpha3 reference                                       | rc1 reference                                                  |
| ------------------------------------------------------ | -------------------------------------------------------------- |
| `Zend\Expressive\Middleware\DispatchMiddleware`        | `Zend\Expressive\Router\Middleware\DispatchMiddleware`         |
| `Zend\Expressive\Middleware\ImplicitHeadMiddleware`    | `Zend\Expressive\Router\Middleware\ImplicitHeadMiddleware`     |
| `Zend\Expressive\Middleware\ImplicitOptionsMiddleware` | `Zend\Expressive\Router\Middleware\ImplicitOptionsMiddleware`  |
| `Zend\Expressive\Middleware\NotFoundMiddleware`        | `Zend\Expressive\Handler\NotFoundHandler`                      |
| `Zend\Expressive\Middleware\RouteMiddleware`           | `Zend\Expressive\Router\Middleware\PathBasedRoutingMiddleware` | 

Also add the following import statement:

```php
use Zend\Expressive\Router\Middleware\MethodNotAllowedMiddleware;
```

Change the line reading:

```php
    $app->pipe(RouteMiddleware::class);
```

to:

```php
    $app->pipe(PathBasedRoutingMiddleware::class);
    $app->pipe(MethodNotAllowedMiddleware::class);
```

Change the line reading:

```php
    $app->pipe(NotFoundMiddleware::class);
```

to:

```php
    $app->pipe(NotFoundHandler::class);
```

## Changes between alpha3 and rc1

A number of substantial changes were released within the core Expressive
packages. We'll detail them package-by-package, providing cumulative changes
(vs. release-by-release changes), with general changes affecting all packages
listed first.

### General changes

Potentially the most far-reaching change was a decision to no longer compose
response _prototypes_ in classes that need to produce a response, but rather
response _factories_. All classes that previously accepted a
`Psr\Http\Message\ResponseInterface` instance (with the exception of
`Zend\Stratigility\Middleware\DoublePassMiddleware`) now accept a PHP `callable`
capable of producing a `ResponseInterface` instance. The simplest form, using
zend-diactoros, would look like the following:

```php
function () {
    return new Response();
}
```

We made this change for a variety of reasons.

First, not all containers allow marking an instance as non-shared. This meant
that a `ResponseInterface` service would return the same instance each time.
While this is generally fine, as the various `with*()` methods produce new
instances, it falls apart when you write to the response body, as the body is
the one part of a response that is mutable. (We often addressed this fact by
also composing a _stream factory_, for producing an empty body stream to use
with a response.) While we could solve this problem by indicating the
`ResponseInterface` service was non-shared, this would not work on all
containers, and led to convoluted solutions such as using "virtual services" to
refer to discrete instances.

Second, an upcoming specification from [PHP-FIG](https://www.php-fig.org) will
largely address this. The proposed PSR-17 defines a number of interfaces
describing factories for PSR-7 HTTP messages.

By changing our classes to compose a callable factory, we accomplish several
things:

- We can now share the `ResponseInterface` service safely, and re-use it in any
  service that needs to produce a response. Since the factory will _always_
  return a new response instance, sharing the factory for the
  `ResponseInterface` service is safe.

- When PSR-17 is available, we will be able to decorate its response factory via
  a closure and continue to use it. Expressive applications will be immediately
  PSR-17 compatible.

> #### Solving type-safety issues
>
> The one problem with passing a PHP `callable` for the factory is that we have
> no guarantees that it actually returns a PSR-7 `ResponseInterface`!
>
> To solve this, each class that composes a response factory re-assigns it as
> follows:
>
> ```php
> $this->responseFactory = function () use ($responseFactory) : ResponseInterface {
>     return $responseFactory();
> };
> ```
>
> This approach ensures that a `TypeError` is raised if the factory returns any
> other type!

### zend-stratigility

Stratigility received the following changes since the Expressive alpha3 release.

- The `Zend\Stratigility\Middleware\ErrorHandler` and `NotFoundHandler` classes
  were updated to accept response _factories_ instead of _prototypes_, as outlined
  in the previous section.

- All middleware and handlers, as well as the `Next` implementation, were marked
  `final`.

### zend-expressive-router

zend-expressive-router underwent a massive rewrite. The rewrite can be
characterized as two major changes: routes and route results are now middleware,
and all middleware from zend-expressive other than the `LazyLoadingMiddleware`
was moved to this package.

One general change was made: the package now ships a `ConfigProvider` class and
exposes it to zend-component-installer. This allows it to ship factories for
middleware it exposes and ensure that middleware can be used immediately within
Expressive applications.

#### Routes and RouteResults

`Zend\Expressive\Router\Route` now implements the PSR-15 `MiddlewareInterface`.
Its `process()` method proxies to the middleware passed to its constructor.

`Zend\Expressive\Router\RouteResult` also now implements `MiddlewareInterface`.
In the case of a successful result, its `process()` method will proxy to the
route matched. In the case of a failed match, the method instead acts as a
no-op, proxying directly to the handler argument. Due to these changes, we felt
we could remove the `getMatchedMiddleware()` method; the middleware is still
accessible via the composed `Route` class, and, generally speaking, you should
not need direct access to it.

These changes greatly simplify the `DispatchMiddleware`, as it no longer needs
to check if a successful match occurred, and can instead process the route
result directly.

Additionally, the `RouteMiddleware::process()` logic was simplified, as it no
longer needs to conditionally inject a `RouteResult` as a request attribute; it
does it regardless of the result of matching.

#### Middleware

All previously provided middleware (`RouteMiddleware`, `PathBasedRoutingMiddleware`,
and `DispatchMiddleware`) were moved to the `Zend\Expressive\Router\Middleware`
namespace.

Additionally, we moved the following middleware from zend-expressive into this
package:

| zend-expressive middleware class                       | zend-expressive-router middleware class                       |
| ------------------------------------------------------ | ------------------------------------------------------------- |
| `Zend\Expressive\Middleware\ImplicitHeadMiddleware`    | `Zend\Expressive\Router\Middleware\ImplicitHeadMiddleware`    |
| `Zend\Expressive\Middleware\ImplicitOptionsMiddleware` | `Zend\Expressive\Router\Middleware\ImplicitOptionsMiddleware` |

As detailed in the [General changes section](#general-changes), each was
modified to compose a response factory instead of a prototype.

These middleware were imported into zend-expressive-router as they are closely
related to routing:

- `ImplicitHeadMiddleware` introspects a `RouteResult` in order to return a
  response if a `HEAD` request was made and other conditions are met.

- `ImplicitOptionsMiddleware` introspects a `RouteResult` in order to return a
  response if an `OPTIONS` request was made and other conditions are met.

We also refactored the `RouteMiddleware` (and, by extension, the
`PathBasedRoutingMiddleware`) in order to extract the functionality for
indicating a `405 Method Not Allowed` response. This functionality is now
shipped in a new class, `Zend\Expressive\Router\Middleware\MethodNotAllowedMiddleware`,
which should be piped immediately following the `RouteMiddleware` or
`PathBasedRoutingMiddleware`. This solves a problem several users have reported:
we previously had no ability to modify how a 405 response is returned. You can
now pipe alternative middleware for this feature if you desire using templates,
Problem Details, or other formats.

### zend-expressive

The zend-expressive package also had a large number of changes. The majority
were related to _exporting_ functionality to more relevant packages; other
changes were made to provide backwards compatibility with previous alpha
releases, as well as v2 releases.

#### API/Behavior changes.

- `Zend\Expressive\Container\ApplicationConfigInjectionDelegator` now raises an
  exception if the `$callback` argument produces an instance of anything other
  than a `Zend\Expressive\Application` instance. Previously, it would return it
  immediately. Now, it raises an exception in order to detail to the user what
  changes they need to make to their application.

- The exceptions `Zend\Expressive\Exception\InvalidMiddlewareException` and
  `MissingDependencyException` were updated to implement the PSR-11
  `ContainerExceptionInterface`.

#### New classes

- `Zend\Expressive\Response\ServerRequestErrorResponseGenerator` is an invokable
  class capable of generating a response from a `Throwable` as provided by
  `Zend\HttpHandlerRunner\RequestHandlerRunner`. The class
  `Zend\Expressive\Container\ServerRequestErrorResponseGeneratorFactory` was
  updated to create and return an instance of this class.

- `Zend\Expressive\Response\ErrorResponseGeneratorTrait` contains the bulk of
  the logic for generating an error response used both by the above class, as
  well as the existing `Zend\Expressive\Middleware\ErrorResponseGenerator`.

#### New factories

The package provides two new factories:

- `Zend\Expressive\Container\ResponseFactoryFactory` will return a PHP callable
  capable of producing a PSR-7 `ResponseInterface`. By default, the class provides
  a closure around instantiation of a zend-diactoros `Response` instance.  The
  package maps it to the service `Psr\Http\Message\ResponseInterface`.

- `Zend\Expressive\Container\StreamFactoryFactory` will return a PHP callable
  capable of producing a PSR-7 `StreamInterface`. By default, the class provides a
  closure around instantiation of a zend-diactoros `Stream` instance backed by a
  writable `php://temp` resource. The package maps it to the service
  `Psr\Http\Message\StreamInterface`.

#### Middleware removals

The following middleware were removed; in all cases, they were added to the
zend-expressive-router package:

- `Zend\Expressive\Middleware\ImplicitHeadMiddleware`
- `Zend\Expressive\Middleware\ImplicitOptionsMiddleware`

The class `Zend\Expressive\Middleware\NotFoundMiddleware` was renamed to
`Zend\Expressive\Handler\NotFoundHandler`, and now implements the PSR-15
`RequestHandlerInterface` (instead of `MiddlewareInterface`). The class now also
composes a response factory instead of a response prototype. (Its related
factory does this for you automatically.)

#### Factory removals

The following factories were removed, as they are either unnecessary, or
provided by other packages:

- `Zend\Expressive\Container\RouteMiddlewareFactory` (now provided in
  zend-expressive-router)
- `Zend\Expressive\Container\DispatchMiddlewareFactory` (now provided in
  zend-expressive-router)
- `Zend\Expressive\Container\ImplicitHeadMiddlewareFactory` (now provided in
  zend-expressive-router)
- `Zend\Expressive\Container\ImplicitOptionsMiddlewareFactory` (now provided in
  zend-expressive-router)

### zend-expressive-tooling

We spent a fair amount of time on zend-expressive-tooling to provide both
migration tools as well as tools to make you more productive during development.
In particular, we added the following:

- `migrate:interop-middleware` will migrate middleware implementing http-interop
  interfaces to PSR-15. (This was in previous releases, but not highlighted
  before!)

- `migrate:middleware-to-request-handler` will scan a directory (your `src/`
  tree by default) for middleware. When it detects middleware that does not call
  on its handler argument, it converts it to a request handler.

- `action:create` is an alias to the `handler:create` detailed in the previous
  post on alpha3, and exposes the same set of functionality for creating a
  PSR-15 request handler. Some developers prefer the "Action" verbiage, and
  requested this feature.

- `factory:create` will generate a factory class file for the given class, using
  PHP's Reflection API. The generated file will be a sibling to the original
  class file. The functionality also auto-registers the class and factory in
  your configuration.

  The `middleware:create`, `handler:create`, and `action:create` commands now
  all use this functionality to create a factory for the class generated and
  wire it in your application configuration. (You may disable this capability
  via a CLI switch.)

Additionally, we added template awareness to the `handler:create` and
`action:create` commands. By default, if they detect a
`Zend\Expressive\Template\TemplateRendererInterface` service in your container,
they will:

- Generate a template named after the root namespace and newly generated class
  (minus any `Handler`, `Action`, or `Middleware` suffix).
- Place a template file in the configured template path for the root namespace,
  and named after the generated class.
- Modify the generated class to accept a `TemplateRendererInterface` instance to
  its constructor, and render the named template to a zend-diactoros
  `HtmlResponse`.

The commands allow you to disable template capabilities via a switch, as well as
provide an alternate template namespace, template name, and template file
extension.

## Ecosystem updates

The skeleton releases cover the core functionality of Expressive: Stratigility,
Expressive itself, routing, template engines, and the helpers. However, the
Expressive ecosystem includes other functionality as well:

- zend-expressive-session and its adapters
- zend-expressive-authentication and its adapters
- zend-expressive-authorization and its adapters
- zend-problem-details
- zend-expressive-hal

We have provided alpha releases of each of these packages to provide the
following:

- PSR-15 support
- Response factory (vs response prototype) support
- Compatibility with core alpha and RC releases

As such, if you are using the RC1 skeleton (or have updated your alpha3
skeleton), you will be able to use these packages without issue; installing them
will grab these latest alpha versions, which will be compatible with the stable
release (and for which their own stable releases will work with the Expressive
v3 release).

## Roadmap

So, we're done, right?

Wrong!

There's still work that remains. In particular:

- We plan to version the existing documentation. This will allow us to provide
  version-specific docs, without confusing users about different usage and
  declarations.

- We need to provide full documentation for the v3 release.

- We will be issuing a 2.2 release with:
  - Deprecations, based on the v3 changes.
  - Backports of select v3 changes in order to aid migration.

- We need to document migration from v2.2 to v3, and potentially provide
  automated tooling.

- We anticipate users will find bugs in the RC, and will be actively
  incorporating bugfixes before the stable release.

Our target date is still 15 March 2018, but we need your help! Help by testing
the RC1 skeleton and providing your feedback. As we prepare the v2.2 release,
help by testing tooling and applying our migration documentation to your
applications, and let us know what works and what doesn't. If you find features
that are not documented, let us know by filing issues or asking questions in
[our Slack](https://zendframework-slack.herokuapp.com).

We look forward to the stable release, and the positive impact PSR-15 will have
on the PHP ecosystem!
