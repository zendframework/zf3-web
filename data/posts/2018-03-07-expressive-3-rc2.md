---
layout: post
title: Expressive 3.0.0RC2 released
date: 2018-03-07T12:15:00-05:00
author: Matthew Weier O'Phinney
url_author: https://mwop.net
permalink: /blog/2018-03-07-expressive-3-rc2.html
categories:
- blog
- expressive
- psr-15

---

This week, we've worked on backports from Expressive 3 to Expressive 2, and, in
the process, identified a few issues with how the routing package handles
implicit `HEAD` and `OPTIONS` requests. As a result, we've just released
3.0.0rc2:

- https://github.com/zendframework/zend-expressive-skeleton/releases/3.0.0rc2

> #### What are "implicit" HEAD and OPTIONS requests?
> 
> Implicit `HEAD` and `OPTIONS` requests are requests using those methods made to
> routes that do not _explicitly_ define them; in other words, if no routes for a
> given path include the `HEAD` or `OPTIONS` methods.
> 
> We provide a way for router implementations to flag a routing failure as being
> due to requesting a method that is not explicitly allowed. We also provide
> middleware for providing responses to `HEAD` and `OPTIONS` requests under those
> conditions, as well as separate middleware for simply reporting that a method is
> not allowed.

## Getting started with RC2

To start a new project based on 3.0.0rc2, use
[Composer](https://getcomposer.org) to create a new project:

```bash
$ composer create-project "zendframework/zend-expressive-skeleton:3.0.0rc2"
```

If you want to install to a custom directory name, use the following instead:

```bash
$ composer create-project zendframework/zend-expressive-skeleton {your directory} 3.0.0rc2
```

Once installed, you can [follow the same instructions as for RC1](https://framework.zend.com/blog/2018-02-27-expressive-3-rc1.html#getting-started-with-rc1).

## Updating from RC1

Updating from RC1 requires a few manual steps.

Prior to upgrading, you will need to do the following:

```bash
$ composer require "zendframework/zend-diactoros:^1.7.1"
```

Then run:

```bash
$ composer update
```

Once done, you will need to make one change to your `config/pipeline.php`.

Locate the following line:

```php
$app->pipe(MethodNotAllowedMiddleware::class);
```

Cut the line, and paste it following the line reading:

```php
$app->pipe(ImplicitOptionsMiddleware::class);
```

This change is necessary due to how each of these middleware inspect the routing
result and act on it. If `MethodNotAllowedMiddleware` operates before the
`Implicit*Middleware`, it will detect a 405 condition. Moving it after those
middleware allow them to intercept for `HEAD` and `OPTIONS` requests.

## Roadmap

We still have a number of tasks to accomplish before the stable 3.0.0 release.
In particular:

- We need to provide full documentation for the v3 release.

- We will be issuing a 2.2 release with:
  - Deprecations, based on the v3 changes.
  - Backports of select v3 changes in order to aid migration.
  - See the following for full details: https://discourse.zendframework.com/t/roadmap-expressive-2-2/504

- We need to document migration from v2.2 to v3, and potentially provide
  automated tooling.

- We anticipate users may still find bugs in the RC, and will be actively
  incorporating bugfixes before the stable release.

Our target date is still 15 March 2018, but we need your help! Help by testing
the RC2 skeleton and providing your feedback. As we prepare the v2.2 release,
help by testing tooling and applying our migration documentation to your
applications, and let us know what works and what doesn't. If you find features
that are not documented, let us know by filing issues or asking questions in
[our Slack](https://zendframework-slack.herokuapp.com).

We look forward to the stable release, and the positive impact PSR-15 will have
on the PHP ecosystem!
