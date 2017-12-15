---
layout: post
title: Expressive 3 Preview
date: 2017-12-14T12:30:00-05:00
author: Matthew Weier O'Phinney
url_author: https://mwop.net
permalink: /blog/2017-12-14-expressive-3-dev.html
categories:
- blog
- expressive
- psr-15

---

Last week, the PSR-15 working group [voted to start its review
phase](https://groups.google.com/d/msg/php-fig/mfTrFinTvEM/PiYvU2S6BAAJ).
PSR-15 seeks to standardize server-side request handlers and middleware, and
both Stratigility and Expressive have been implementing draft specifications
since their version 2 releases. Entering the review phase is an important
moment: it means that the working group feels the specification is stable and
ready for adoption. If, after the review period is over, no major changes are
required, the specification can be presented to the PHP-FIG core committed for a
final acceptance vote, at which point it will be frozen and ready for mass
adoption.

Our plan is to have Stratigility and Expressive follow the new specification in
its final form. To that end, we have been executing on a plan to prepare all our
projects that work with PSR-15 to adopt the latest round of changes.

That work is ready today!

## What has changed in PSR-15?

The latest round of changes to the specification prior to entering the review
period were as follows:

- The namespace of the draft specification was changed from
  `Interop\Http\ServerMiddleware` to `Interop\Http\Server`. These will therefor
  become `Psr\Http\Server` once the specification is accepted.

- The `DelegateInterface` was renamed to `RequestHandlerInterface`, and
  the method it defines renamed to `handle()`.

- The `MiddlewareInterface`'s second argument to `process()` was updated to
  typehint against `RequestHandlerInterface`.

- The package shipping the interface was split into two,
  `http-interop/http-server-handler` and `http-interop/http-server-middleware`;
  these will become `psr/http-server-handler` and `psr/http-server-middleware`,
  respectively, once the package is accepted. The `http-server-middleware`
  packages depend on the `http-server-handler` packages.

These changes, of course, are not backwards compatible, and our attempts to
write a polyfill library were ultimately unsuccessful. As a result, we decided
to bump the major version of all libraries currently depending on the draft
specification.

## What we have done

Our approach in updating the various packages was as follows:

- We created a new release branch named after the next major release. For
  instance, if a library is currently issuing v2 releases, we created a
  `release-3.0.0` branch.
- We updated the branch aliases defined in the `composer.json` for the package
  as follows, on all branches:
  - The master branch points to the current minor release. As an example, for a
    package with a current stable 2.3.1 version, the branch alias became
    `"dev-master": "2.3.x-dev"`.
  - If a development branch already exists, we updated similarly to the master
    branch. For the above example, the branch alias would read `"dev-develop":
    "2.4.x-dev"`.
  - The new release branch is then mapped to the upcoming major version:
  `"dev-release-3.0.0": "3.0.x-dev".
- On the release branches, we updated dependencies as follows:
  - PHP dependencies became simply `^7.1` (per [our decision posted in
    June](https://framework.zend.com/blog/2017-06-06-zf-php-7-1.html)).
  - References to `http-interop/http-middleware` packages were changed to
    `"http-interop/http-server-middleware": "^1.0.1"`.
  - References to packages that have corresponding release branches were updated
    to have their constraints point to the appropriate development release branch.
    As an example, `"zendframework/zend-expressive-router": "^3.0.0-dev"`.

These changes ensure users can install the new development versions of packages
by feeding an appropriate development constraint.

You'll note that we bumped the minimum supported PHP version in these packages
as well. Because we were doing that, we also decided to make use of PHP 7.1
features. In particular:

- Scalar and return type hints.
- Nullable and void types.
- Null coalesce.
- `strict_types` where it simplifies validation of scalars (which turns out to
  be almost everywhere).

For packages that define interfaces, this meant that we also needed
corresponding major version bumps in packages that implement those interfaces.
This affected the router and template implementations in particular.

If you want a complete list of what was updated, you can visit the [burndown
list in the forums](https://discourse.zendframework.com/t/psr-15-compatibility-issues-and-proposal/378/11).

## How YOU can test

This is all very nice and technical, but how can YOU test out the new versions?

Install the development version of the Expressive skeleton!

```bash
$ composer create-project "zendframework/zend-expressive-skeleton:3.0.x-dev" expressive-3.0-dev
```

This will create the skeleton project, with your selected functionality, in a
directory named `expressive-3.0-dev`. From there, you can start developing!

When you do, be aware of the following:

- Middleware must now implement `Interop\Http\Server\MiddlewareInterface`:

  ```php
  namespace YourModule;

  use Interop\Http\Server\MiddlewareInterface;
  use Interop\Http\Server\RequestHandlerInterface;
  use Psr\Http\Message\ResponseInterface;
  use Psr\Http\Message\RequestHandlerInterface;

  class YourMiddleware implements MiddlewareInterface
  {
      public function process(
          ServerRequestInterface $request,
          RequestHandlerInterface $handler
      ) : ResponseInterface {
      }
  }
  ```

  Note: `vendor/bin/expressive middleware:create` will create these correctly
  for you with its 1.0.0-dev release!

- If you want to delegate handling to the next middleware, you will now use the
  `$handler`, and call its `handle()` method:

  ```php
  $response = $handler->handle($request);
  ```

- If you want to use one of the optional Expressive packages, such as
  zend-expressive-session, you will need to require it using a development
  constraint. For instance:

  ```bash
  $ composer require zendframework/zend-expressive-session:^1.0.0-dev
  ```

  Note the use of the semantic pin (`^`), as well as the `-dev` suffix; both are
  necessary for composer to identify the development release.

Regarding the last point, the following is a list of all packages with
development release branches, along with the corresponding version you should
use when requiring them while testing:

| Package                               | Version      |
|:------------------------------------- |:------------:|
| zend-expressive                       | `^3.0.0-dev` |
| zend-expressive-aurarouter            | `^3.0.0-dev` |
| zend-expressive-authentication        | `^1.0.0-dev` |
| zend-expressive-authentication-oauth2 | `^1.0.0-dev` |
| zend-expressive-authorization         | `^1.0.0-dev` |
| zend-expressive-csrf                  | `^1.0.0-dev` |
| zend-expressive-fastroute             | `^3.0.0-dev` |
| zend-expressive-flash                 | `^1.0.0-dev` |
| zend-expressive-helpers               | `^5.0.0-dev` |
| zend-expressive-plastesrenderer       | `^2.0.0-dev` |
| zend-expressive-router                | `^3.0.0-dev` |
| zend-expressive-session               | `^1.0.0-dev` |
| zend-expressive-skeleton              | `^3.0.0-dev` |
| zend-expressive-template              | `^2.0.0-dev` |
| zend-expressive-tooling               | `^1.0.0-dev` |
| zend-expressive-twigrenderer          | `^2.0.0-dev` |
| zend-expressive-zendrouter            | `^3.0.0-dev` |
| zend-expressive-zendviewrenderer      | `^2.0.0-dev` |
| zend-problem-details                  | `^1.0.0-dev` |
| zend-stratigility                     | `^3.0.0-dev` |

In most cases, unless you are extending classes we provide, your existing code
should just work with the new packages once you update your middleware to the
new signatures.

> ### Updating an existing application
>
> Updating an existing application requires a bit more effort. You will need to
> manually edit your `composer.json` to update the constraints for each of the
> above packages to match what is in the table. Additionally, if you see
> references to either `http-interop/http-middleware` or
> `webimpress/http-middleware-compatibility`, you will need to remove those.
> You will also need to add the following two lines to the file:
>
> ```json
> "minimum-stability": "dev",
> "prefer-stable": true
> ```
>
> Once done with the `composer.json` changes, run `composer update` to pick up
> the changes. If you encounter any issues, run `rm -Rf composer.lock vendor`, and
> then execute `composer install`.
>
> Finally, you will need to update any middleware in your application to
> implement the new interface. Ensure you have `zend-expressive-tooling`
> installed, and install it if you do not, using the `^1.0.0-dev` constraint
> (`composer require --dev "zendframework/zend-expressive-tooling:^1.0.0-dev"`).
> Once you do, run:
>
> ```bash
> $ ./vendor/bin/expressive migrate:interop-middleware
> ```

## What's next?

If you run into things that do not work, report them on the appropriate issue
tracker.

Once PSR-15 is finalized, our plan is to go through and update each package
depending directly on it to point to the new PHP-FIG sponsored packages, and
update import statements throughout our code appropriately. We'll then likely
issue a beta release for folks to test against one last time.

In the meantime, we'll also be looking at other changes we may want to make. New
major version breaks should happen only rarely going forward, and we may want to
make a few more changes to help improve quality, simplify maintenance, and
increase usability before we make the final release. As we do, we'll update you
here on the blog.

> ## Want some ebooks on ZF and Expressive?
>
> We collated our posts from the first half of 2017 into two ebooks:
>
> - **Zend Framework 3 Cookbook**, which covers usage of a couple dozen ZF
>   components, within zend-mvc and Expressive applications, as well as
>   standalone.
> - **Expressive Cookbook**, which covers features of Expressive and middleware
>   in general.
>
> You can [get them free with registration on the zend.com
> website](https://www.zend.com/phpcookbooks).
