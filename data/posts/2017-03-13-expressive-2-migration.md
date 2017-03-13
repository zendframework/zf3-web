---
layout: post
title: Migrating to Expressive 2.0
date: 2017-03-13T13:10:00-05:00
author: Matthew Weier O'Phinney
url_author: https://mwop.net/
permalink: /blog/2017-03-13-expressive-2-migration.html
categories:
- blog
- expressive
- psr-7
- psr-15

---

Last week, [we released Expressive 2](/blog/2017-03-07-expressive-2.html).
A new major version implies breaking changes, which often poses a problem when
migrating. That said, we did a lot of work behind the scenes to try and ensure
that migrations can happen without too much effort, including providing
migration tools to ease the transition.

In this tutorial, we will detail migrating an existing Expressive application
from version 1 to version 2.

> ### How we tested this
> 
> We used Adam Culp's [expressive-blastoff](https://github.com/adamculp/expressive-blastoff)
> repository as a test-bed for this tutorial, and you can follow along from there
> if you want, by checking out the 1.0 tag of that repository:
> 
> ```bash
> $ git clone https://github.com/adamculp/expressive-blastoff
> $ cd expressive-blastoff
> $ git checkout 1.0
> $ composer install
> ```
>
> We have also successfully migrated a number of other applications, including
> the Zend Framework website itself, using essentially the same approach. As is
> the case with any such tutorial, your own experience may vary.

## Updating dependencies

First, create a new feature branch for the migration, to ensure you do not
clobber working code. If you are using git, this might look like this:

```bash
$ git checkout -b feature/expressive-2
```

If you have not yet installed dependencies, we recommend doing so:

```php
$ composer install
```

Now, we'll update dependencies to get Expressive 2. Doing so on an existing
project requires a number of other updates as well:

- You will need to update whichever router implementation you use, as we have
  released new major versions of all routers, to take advantage of a new major
  version of the zend-expressive-router `RouterInterface`. You can pin these to
  `^2.0`.

- You will need to update the zend-expressive-helpers package, as it now also
  depends on the new `RouterInterface` changes. You can pin this to `^3.0`.

- You will need to update your template renderer, if you have one installed.
  These received minor version bumps in order to add compatibility with the new
  zend-expressive-helpers release; however, since we'll be issuing a `require`
  statement to upgrade Expressive, we need to specify the new template renderer
  version as well. Constraints for the supported renderers are:

  - `zendframework/zend-expressive-platesrenderer:^1.2`
  - `zendframework/zend-expressive-twigrenderer:^1.3`
  - `zendframework/zend-expressive-zendviewrenderer:^1.3`

As an example, if you are using the recommended packages
zendframework/zend-expressive-fastroute and zendframework/zend-expressive-platesrenderer,
you will update to Expressive 2.0 using the following statement:

```bash
$ composer update --with-dependencies "zendframework/zend-expressive:^2.0" \
> "zendframework/zend-expressive-fastroute:^2.0" \
> "zendframework/zend-expressive-helpers:^3.0" \
> "zendframework/zend-expressive-platesrenderer:^1.2"
```

At this point, try out your site. In many cases, it _should_ continue to "just
work."

> ### Common errors
>
> We say _should_ for a reason. There are a number of features that will not
> work, but were not commonly used by end-users, including accessing properties
> on the request/response decorators that Stratigility 1 shipped (on which Expressive 1
> was based), and usage of Stratigility 1 "error middleware" (which was removed in
> the version 2 releases). While these were documented, many users were not aware
> of the features and/or did not use them. If you did, however, you will notice
> your site will not run following the upgrade. Don't worry; we cover tools that
> will solve these issues in the next section!

## Migration

At this point, there's a few more steps you should take to fully migrate your
application; in some cases, your application is currently broken, and will
require these changes to work in the first place!

We provide CLI tooling that assists in these migrations via the package
zendframework/zend-expressive-tooling. Add this as a development requirement to
your application now:

```bash
$ composer require --dev --update-with-dependencies zendframework/zend-expressive-tooling
```

(The `--update-with-dependencies` may be necessary to pick up newer versions of
zend-stdlib and zend-code, among others.)

Expressive 1 was based on Stratigility 1, which decorated the request and
response objects with wrappers that provide access to the original incoming
request, URI, and response. With Stratigility 2 and Expressive 2, these
decorators have been removed; however access to these artifacts is available via
request attributes. As such, we provide a tool to scan for usage of these and
fix them when possible. Let's invoke it now:

```bash
$ ./vendor/bin/expressive-migrate-original-messages scan
```

(If your code is in a directory other than `src/`, then use the `--help` switch
for options on specifying that directory.)

Most likely the tool won't find anything. In some cases, it *will* find
something, and try to correct it. The one thing it cannot correct are calls to
`getOriginalResponse()`; in such cases, the tool details how to correct those
problems, and in what files they occur.

Next, we'll scan for legacy error middleware. This was middleware defined in
Stratigility with an alternate signature:


```php
function (
    $error,
    ServerRequestInterface $request,
    ResponseInterface $response,
    callable $next
) : ResponseInterface
```

Such middleware was invoked by calling `$next` with a third argument:

```php
$response = $next($request, $response, $error);
```

This style of middleware has been removed from Stratigility 2 and Expressive 2,
and will not work at all. We provide another tool for finding both error
middleware, as well as invocations of error middleware:

```bash
$ ./vendor/bin/expressive-scan-for-error-middleware scan
```

(If your code is in a directory other than `src/`, then use the `--help` switch
for options on specifying that directory.)

This tool does not change any code, but it will tell you files that contain
problems, and give you information on how to correct the issues.

Finally, we'll migrate to a _programmatic pipeline_. In Expressive 1, the
skeleton defined the pipeline and routes via configuration. Many users have
indicated that using the Expressive API tends to be easier to learn and
understand than the configuration; additionally, IDEs and static analyzers are
better able to determine if programmatic pipelines and routing are correct than
configuration-driven ones.

As with the other migration tasks, we provide a tool for this:

```bash
$ ./vendor/bin/expressive-pipeline-from-config generate
```

This tool loads your existing configuration, and then does the following:

- Creates `config/autoload/programmatic-pipeline.global.php`, which contains
  directives to tell Expressive to ignore configured pipelines and routing,
  and defines dependencies for new error handling and pipeline middleware.
- Creates `config/pipeline.php` with your application middleware pipeline.
- Creates `config/routes.php` with your application routing definitions.
- Updates `public/index.php` to include the above two files prior to calling
  `$app->run()`.

The tool will also tell you if it encounters legacy error middleware in your
configuration; if it does, it skips adding directives to compose it in the
application pipeline, but notifies you it is doing so. Be aware of that, if you
depended on the feature previously; in most cases, if you've been following this
tutorial step-by-step, you've already eliminated them.

At this point, try out your application again! If all went well, this should
"just work." 

## Bonus steps!

While the above will get your application migrated, V2 of the skeleton
application offers three additional features that were not present in the
original v1 releases:

- self-invoking function in `public/index.php` in order to prevent global variable
  declarations.
- ability to define and/or use middleware modules, via zend-config-aggregator.
- development mode.

### Self-invoking function

The point of this change is to prevent addition of variables into the `$GLOBAL`
scope. This is done by creating a _self-invoking function_ around the directives
in `public/index.php` that create and use variables.

After completing the earlier steps, you should have lines like the following in
your `public/index.php`:

```php
/** @var \Interop\Container\ContainerInterface $container */
$container = require 'config/container.php';

/** @var \Zend\Expressive\Application $app */
$app = $container->get(\Zend\Expressive\Application::class);
require 'config/pipeline.php';
require 'config/routes.php';
$app->run();
```

We'll create a self-invoking function around them. If you are using PHP 7+, this
looks like the following:

```php
(function () {
  /** @var \Interop\Container\ContainerInterface $container */
  $container = require 'config/container.php';

  /** @var \Zend\Expressive\Application $app */
  $app = $container->get(\Zend\Expressive\Application::class);
  require 'config/pipeline.php';
  require 'config/routes.php';
  $app->run();
})();
```

If you're still using PHP 5.6, you need to use `call_user_func()`:

```php
call_user_func(function () {
  /** @var \Interop\Container\ContainerInterface $container */
  $container = require 'config/container.php';

  /** @var \Zend\Expressive\Application $app */
  $app = $container->get(\Zend\Expressive\Application::class);
  require 'config/pipeline.php';
  require 'config/routes.php';
  $app->run();
});
```

### zend-config-aggregator

zendframework/zend-config-aggregator is at the heart of the [modular middleware
system](https://docs.zendframework.com/zend-expressive/features/modular-applications/).
It works as follows:

- _Modules_ are just libraries or packages that define a `ConfigProvider` class.
  These classes are stateless and define an `__invoke()` method that returns an
  array of configuration.
- The `config/config.php` file then uses
  `Zend\ConfigAggregator\ConfigAggregator` to, well, _aggregate configuration_
  from a variety of sources, including `ConfigProvider` classes, as well as
  other specialized providers (e.g., PHP file provider for aggregating PHP
  configuration files, array provider for supplying hard-coded array
  configuration, etc.). This package provides built-in support for configuration
  caching as well.

We also provide a Composer plugin, zend-component-installer, that works with
configuration files that utilize the `ConfigAggregator`. It executes during
install operations, and checks the package being installed for configuration
indicating it provides a `ConfigProvider`; if so, it will then prompt you,
asking if you want to add it to your configuration. This is a great way to
automate addition of dependencies and module-specific configuration to your
application!

To get started, let's add zend-config-aggregator to our application:

```bash
$ composer require zendframework/zend-config-aggregator
```

We'll also add the `zend-component-installer`, but as a development requirement
only:

```bash
$ composer require --dev zendframework/zend-component-installer
```

(Note: it will likely already have been installed with zend-expressive-tooling;
requiring it like this, however, ensures it stays present if you decide to
remove that package later.)

To update your application, you will need to update your `config/config.php`
file.

If you've made no modifications to the shipped version, it will look like the
following:

```php
<?php

use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\Glob;

/**
 * Configuration files are loaded in a specific order. First ``global.php``, then ``*.global.php``.
 * then ``local.php`` and finally ``*.local.php``. This way local settings overwrite global settings.
 *
 * The configuration can be cached. This can be done by setting ``config_cache_enabled`` to ``true``.
 *
 * Obviously, if you use closures in your config you can't cache it.
 */

$cachedConfigFile = 'data/cache/app_config.php';

$config = [];
if (is_file($cachedConfigFile)) {
    // Try to load the cached config
    $config = include $cachedConfigFile;
} else {
    // Load configuration from autoload path
    foreach (Glob::glob('config/autoload/{{,*.}global,{,*.}local}.php', Glob::GLOB_BRACE) as $file) {
        $config = ArrayUtils::merge($config, include $file);
    }

    // Cache config if enabled
    if (isset($config['config_cache_enabled']) && $config['config_cache_enabled'] === true) {
        file_put_contents($cachedConfigFile, '<?php return ' . var_export($config, true) . ';');
    }
}

// Return an ArrayObject so we can inject the config as a service in Aura.Di
// and still use array checks like ``is_array``.
return new ArrayObject($config, ArrayObject::ARRAY_AS_PROPS);
```

You can replace it directly with this, then:

```php
<?php

use Zend\ConfigAggregator\ArrayProvider;
use Zend\ConfigAggregator\ConfigAggregator;
use Zend\ConfigAggregator\PhpFileProvider;

$cacheConfig = [
    'config_cache_path' => 'data/config-cache.php',
];

$aggregator = new ConfigAggregator([
    new ArrayProvider($cacheConfig),

    new PhpFileProvider('config/autoload/{{,*.}global,{,*.}local}.php'),
], $cacheConfig['config_cache_path']);

return $aggregator->getMergedConfig();
```

If you want, you can set the `config_cache_path` to match the one from your
previous version; this should only be necessary if you have tooling already in
place for cache clearing, however.

> ### ZF components
>
> Any Zend Framework component that provides service configuration exposes
> a `ConfigProvider`. This means that if you add these to your application after
> making the above changes, they will expose their services to your application
> immediately following installation!
>
> If you've installed ZF components prior to this change, check to see which
> ones expose `ConfigProvider` classes (you can look for a `ConfigProvider` under
> their namespace, or look for an `extra.zf.config-provider` declaration in their
> `composer.json`). If you find any, add them to your `config/config.php` file;
> using the fully qualified class name of the provider. As an example:
> `\Zend\Db\ConfigProvider::class`.

### Development mode

We have been using zf-development-mode with zend-mvc and Apigility applications
for a few years now, and feel it offers an elegant solution for shipping
standard development configuration for use with your team, as well as toggling
back and forth between development and production configuration. (That said,
`config/autoload/*.local.php` files may clearly vary in your development
environment versus your production environment, so this is not entirely
fool-proof!)

Let's add it to our application:

```bash
$ composer require --dev zfcampus/zf-development-mode
```

Note that we're adding it as a development requirement; chances are, you do not
want to accidentally enable it in production!

Next, we need to add a couple files to our tree. The first we'll add is
`config/development.config.php.dist`, with the following contents:

```php
<?php

/**
 * File required to allow enablement of development mode.
 *
 * For use with the zf-development-mode tool.
 *
 * Usage:
 *  $ composer development-disable
 *  $ composer development-enable
 *  $ composer development-status
 *
 * DO NOT MODIFY THIS FILE.
 *
 * Provide your own development-mode settings by editing the file
 * `config/autoload/development.local.php.dist`.
 *
 * Because this file is aggregated last, it simply ensures:
 *
 * - The `debug` flag is _enabled_.
 * - Configuration caching is _disabled_.
 */

use Zend\ConfigAggregator\ConfigAggregator;

return [
    'debug' => true,
    ConfigAggregator::ENABLE_CACHE => false,
];
```

Next, we'll add a `config/autoload/development.local.php.dist`. The contents of
this one will vary based on what you are using in your application.

If you are **not** using Whoops for error reporting, start with this:

```php
<?php
return [
];
```

If you are, this is a chance to configure that correctly for your newly updated
application. Create the file with these contents:

```php
<?php

use Whoops\Handler\PrettyPageHandler;
use Zend\Expressive\Container;
use Zend\Expressive\Middleware\ErrorResponseGenerator;
use Zend\Expressive\Whoops;
use Zend\Expressive\WhoopsPageHandler;

return [
    'dependencies' => [
        'invokables' => [
            WhoopsPageHandler::class => PrettyPageHandler::class,
        ],
        'factories' => [
            ErrorResponseGenerator::class => Container\WhoopsErrorResponseGeneratorFactory::class,
            Whoops::class => Container\WhoopsFactory::class,
        ],
    ],

    'whoops' => [
        'json_exceptions' => [
            'display'    => true,
            'show_trace' => true,
            'ajax_only'  => true,
        ],
    ],
];
```

Next, if you started with the V1 skeleton application, you will likely have a
file named `config/autoload/errorhandler.local.php`, and it will have similar
contents, for the purpose of seeding the legacy "final handler" system. You can
now _remove_ that file.

After that's done, we need to add some directives so that git will ignore the
non-dist files. Edit the `.gitignore` file in your project's root directory to
add the following entry:

```text
config/development.config.php
```

The `config/autoload/.gitignore` file should already have a rule that omits
`*.local.php`.

Now we need to have our configuration load the development configuration if it's
present. The following assumes you already converted your application to use
zend-config-aggregator. Add the following line as the last element of the array
passed when instantiating your `ConfigAggregator`:

```php
    new PhpFileProvider('config/development.config.php'),
```

If the file is missing, that provider will return an empty array; if it's
present, it returns whatever configuration the file returns. By making it the
last element merged, we can do things like override configuration caching, and
force debug mode, which is what our `config/development.config.php.dist` file
does!

Finally, let's add some convenience scripts to composer. Open your
`composer.json` file, find the `scripts` section, and add the following to it:

```json
        "development-disable": "zf-development-mode disable",
        "development-enable": "zf-development-mode enable",
        "development-status": "zf-development-mode status",
```

Now we can try it out!

Run:

```bash
$ composer development-status
```

This should tell you that development mode is currently disabled.

Next, run:

```bash
$ composer development-enable
```

This will enable development mode. 

If you want to test and ensure you're in development mode, edit one of your
middleware to have it raise an exception, and see what happens!

## Clean up

If your application is working correctly, you can now do some additional
cleanup.

- Edit your `config/autoload/middleware-pipeline.global.php` file to remove the
  `middleware_pipeline` key and its contents.
- Edit your `config/autoload/routes.global.php` file to remove the
  `routes` key and its contents.
- Search for any references to a `FinalHandler` within your dependency
  configuration, and remove them.

At this point, you should have a fully working Expressive 2 application!

## Final step: Updating your middleware

Now that the initial migration is complete, you can take some more steps!

One of the big changes is that Expressive 2 _prefers_ middleware implementing
http-interop/http-middleware's `MiddlewareInterface`. This requires a few
changes to your middleware.

First, let's look at the interfaces defined by http-interop/http-middleware:

```php
namespace Interop\Http\ServerMiddleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface MiddlewareInterface
{
    /**
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate);
}

interface DelegateInterface
{
    /**
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request);
}
```

The first interface defines middleware. Unlike Expressive 1, http-interop
middleware does not receive a response instance. There are a variety of reasons
for this, but [Anthony Ferrara sums them up best](http://blog.ircmaxell.com/2016/05/all-about-middleware.html).

Another difference is that instead of a `callable $next` argument, we have a
`DelegateInterface $delegate`. This provides better type-safety, and, because
each of the `MiddlewareInterface` and `DelegateInterface` define the same
`process()` method, ensures that implementations of middleware and delegates are
discrete and do not mix concerns. _Delegates_ are classes that can process a
request if the _current middleware_ cannot fully do so. Examples might include
middleware that will inject additional response headers, or middleware that only
acts when certain request criteria are present (such as HTTP caching headers).

The upshot is that when rewriting your middleware to use the new interfaces, you
need to do several things:

- First, import the http-interop interfaces into your class file:

  ```php
  use Interop\Http\ServerMiddleware\DelegateInterface;
  use Interop\Http\ServerMiddleware\MiddlewareInterface;
  ```

- Second, rename the `__invoke()` method to `process()`.

- Third, update the signature of your new `process` method to be:

  ```php
  public function process(ServerRequestInterface $request, DelegateInterface $delegate)
  ```

- Fourth, look for calls to `$next()`. As an example, the following:

  ```php
  return $next($request, $response);
  ```

  Becomes:

  ```php
  return $delegate->process($request);
  ```

  These updates will vary on a case-by-case basis: in some cases, you may be
  calling methods on the request instance; in other cases, you may be capturing
  the returned response

- Look for cases where you were using the passed `$response` instance, and
  eliminate those. You may do so as follows:

  - Use the response returned by calling `$delegate->process()` instead.
  - Create a new concrete response instance and operate on it.
  - Compose a "response prototype" in your middleware if you do not want to
    create a new response instance directly, and operate on it. Doing so will
    require that you update any factory associated with the middleware class,
    however.

As an example, let's look at a simple middleware that adds a response header:

```php
namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TheClacksMiddleware
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $response = $next($request, $response);
        
        return $response->withHeader('X-Clacks-Overhead', ['GNU Terry Pratchett']);
    }
}
```

When we refactor it to be http-interop middleware, it becomes:

```php
namespace App\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;

class TheClacksMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $response = $delegate->process($request);
        
        return $response->withHeader('X-Clacks-Overhead', ['GNU Terry Pratchett']);
    }
}
```

## Summary

Migration consists of:

- Updating dependencies.
- Running migration scripts provided by zendframework/zend-expressive-tooling.
- Optionally adding a self-invoking function around code creating variables in
  `public/index.php`.
- Optionally updating your application to use zendframework/zend-config-aggregator
  for configuration aggregation.
- Optionally adding zfcampus/zf-development-mode integration to your
  application.
- Optionally updating your middleware to implement http-interop/http-middleware.

As noted, many of these changes are optional. Your application will continue to
run without them. Updating them will modernize your application, however, and
make it more familiar to developers familiar with the Expressive 2 skeleton.

We hope this guide gets you successfully migrated! If you run into issues not
covered here, please let us know via an [issue on the Expressive 
repository](https://github.com/zendframework/zend-expressive/issues/new).
