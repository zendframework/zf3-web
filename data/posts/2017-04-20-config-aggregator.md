---
layout: post
title: Manage your application with zend-config-aggregator
date: 2017-04-20T13:20:00-05:00
author: Matthew Weier O'Phinney
url_author: https://mwop.net/
permalink: /blog/2017-04-20-config-aggregator.html
categories:
- blog
- components
- zend-config-aggregator

---

With the rise of PHP middleware, many developers are creating custom
application architectures, and running into an issue many frameworks already
solve: how to allow runtime configuration of the application.

configuration is often necessary, even in custom applications:

- Some configuration, such as API keys, may vary between environments.
- You may want to substitute services between development and production.
- Some code may be developed by other teams, and pulled into your application
  separately (perhaps via [Composer](https://getcomposer.org)), and require
  configuration.
- You may be writing code in your application that you will later want to share
  with another team, and recognize it should provide service wiring information
  or allow for dynamic configuration itself.

Faced with this reality, you then have a new problem: how can you configure your
application, as well as aggregate configuration from other sources?

As part of the Expressive initiative, we now offer a standalone solution for
you: [zend-config-aggregator](https://github.com/zendframework/zend-config-aggregator)

## Installation

First, you will need to install zend-config-aggregator:

```bash
$ composer require zendframework/zend-config-aggregator
```

One feature of zend-config-aggregator is the ability to consume multiple
configuration formats via [zend-config](https://docs.zendframework.com/zend-config/).
If you wish to use that feature, you will also need to install that package:

```bash
$ composer require zendframework/zend-config
```

Finally, if you are using the above, and want to parse YAML files, you will need
to install the [YAML PECL extension](http://www.php.net/manual/en/book.yaml.php).

## Configuration providers

zend-config-aggregator allows you to aggregate configuration from configuration
_providers_. A configuration provider is any PHP callable that will return an
associative array of configuration.

By default, the component provides the following providers out of the box:

- `Zend\ConfigAggregator\ArrayProvider`, which accepts an array of configuration
  and simply returns it. This is primarily useful for providing global defaults
  for your application.
- `Zend\ConfigAggregator\PhpFileProvider`, which accepts a glob pattern
  describing PHP files that each return an associative array. When invoked, it
  will loop through each file, and merge the results with what it has previously
  stored.
- `Zend\ConfigAggregator\ZendConfigProvider`, which acts similarly to the
  `PhpFileProvider`, but which can aggregate any format
  [zend-config](https://docs.zendframework.com/zend-config) supports, including
  INI, XML, JSON, and YAML.

More interestingly, however, is the fact that you can write providers as simple
invokable objects:

```php
namespace Acme;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            // associative array of configuration
        ];
    }
}
```

This feature allows you to write configuration for specific application
_features_, and then seed your application with it. In other words, this feature
can be used as the foundation for a [_modular
architecture_](https://docs.zendframework.com/zend-expressive/features/modular-applications/),
which is exactly what we did with Expressive!

> ### Generators
>
> You may also use invokable classes or PHP callables that define generators as
> configuration providers! As an example, the `PhpFileProvider` could
> potentially be rewritten as follows:
>
> ```php
> use Zend\Stdlib\Glob;
> 
> function () {
>     foreach (Glob::glob('config/*.php', Glob::GLOB_BRACE) as $file) {
>         yield include $file;
>     }
> }
> ```

## Aggregating configuration

Now that you have configuration providers, you can aggregate them.

For the purposes of this example, we'll assume the following:

- We will have a single configuration file, `config.php`, at the root of our
  application which will aggregate all other configuration.
- We have a number of configuration files under `config/`, including YAML, JSON,
  and PHP files.
- We have a third-party "module" that exposes the class
  `Umbrella\ConfigProvider`.
- We have developed our own "module" for re-distribution that exposes the class
  `Blanket\ConfigProvider`.

Typically, you will want aggregate configuration such that third-party
configuration is loaded first, with application-specific configuration merged
last, in order to override settings.

Let's aggregate and return our configuration.

```php
// in config.php:
use Zend\ConfigAggregator\ConfigAggregator;
use Zend\ConfigAggregator\ZendConfigProvider;

$aggregator = new ConfigAggregator([
    \Umbrella\ConfigProvider::class,
    \Blanket\ConfigProvider::class,
    new ZendConfigProvider('config/*.{json,yaml,php}'),
]);

return $aggregator->getMergedConfig();
```

This file aggregates the third-party configuration provider, the one we expose
in our own application, and then aggregates a variety of different configuration
files in order to, in the end, return an associative array representing the
merged configuration!

> ### Valid config profider entries
>
> You'll note that the `ConfigAggregator` expects an array of providers as the
> first argument to the constructor. This array may consist of any of the
> following:
>
> - Any PHP callable (functions, invokable objects, closures, etc.) returning an
>   array.
> - A class name of a class that defines `__invoke()`, and which requires no
>   constructor arguments.
>
> This latter is useful, as it helps reduce operational overhead once you
> introduce caching, which we discuss below. The above example demonstrates this
> usage.

> ### zend-config and PHP configuration
>
> The above example uses only the `ZendConfigProvider`, and not the
> `PhpFileProvider`. This is due to the fact that zend-config can also consume
> PHP configuration.
>
> If you are only using PHP-based configuration files, you can use the
> `PhpFileProvider` instead, as it does not require additionally installing the
> zendframework/zend-config package.

> ### Globbing and precedence
>
> Globbing works as it does on most *nix systems. As such, you need to pay
> particular attention to when you use patterns that define alternatives, such
> as the `{json,yaml,php}` pattern above. In such cases, all JSON files will be
> aggregated, followed by YAML files, and finally PHP files. If you need them
> to aggregate in a different order, you will need to change the pattern.

## Caching

You likely do not want to aggregate configuration on each and every application
request, particularly if doing so would result in many filesystem hits.
Fortunately, zend-config-aggregator also has built-in caching features.

To enable these features, you will need to do two things:

- First, you need to provide a second argument to the `ConfigAggregator`
  constructor, specifying the path to the cache file to create and/or use.
- Second, you need to enable caching in your configuration, by specifying a
  boolean `true` value for the key `ConfigAggregator::ENABLE_CACHE`.

One common strategy is to enable caching by default, and then disable it via
environment-specific configuration.

We'll update the above example now to enable caching to the file
`cache/config.php`:

```php
use Zend\ConfigAggregator\ArrayProvider;
use Zend\ConfigAggregator\ConfigAggregator;
use Zend\ConfigAggregator\PhpFileProvider;
use Zend\ConfigAggregator\ZendConfigProvider;

$aggregator = new ConfigAggregator(
    [
        new ArrayProvider([ConfigAggregator::ENABLE_CACHE => true]),
        \Umbrella\ConfigProvider::class,
        \Blanket\ConfigProvider::class,
        new ZendConfigProvider('config/{,*.}global.{json,yaml,php}'),
        new PhpFileProvider('config/{,*.}local.php'),
    ],
    'cache/config.php'
);

return $aggregator->getMergedConfig();
```

The above adds an initial setting that enables the cache, and tells it to cache
it to `cache/config.php`.

Notice also that this example changes the `ZendConfigProvider`, and adds a
`PhpFileProvider` entry. Let's examine these.

The `ZendConfigProvider` glob pattern now looks for files named `global` with
one of the accepted extensions, or those named `*.global` with one of the
accepted extensions. This allows us to segregate configuration that should
_always_ be present from environment-specific configuration.

We then add a `PhpFileProvider` that aggregates `local.php` and/or `*.local.php`
files specifically. An interesting side-note about the shipped providers is that
if no matching files are found, the provider will return an empty array; this
means that we can have this additional provider that is looking for separate
configurations for the "local" environment! Because this provider is aggregated
last, the settings it exposes will override any others.

As such, if we want to _disable_ caching, we can create a file such as
`config/local.php` with the following contents:

```php
<?php
use Zend\ConfigAggregator\ConfigAggregator;

return [ConfigAggregator::ENABLE_CACHE => false];
```

and the application will no longer cache aggregated configuration!

> ### Clear the cache!
>
> The setting outlined above is used to determine whether the configuration
> cache file _should be created if it does not already exist_.
> zend-config-aggregator, when provided the location of a configuration cache
> file, will load directly from it if the file is present.
>
> As such, if you make the above configuration change, you will first need to
> remove any cached configuration:
>
> ```bash
> $ rm cache/config.php
> ```
>
> This can even be made into a Composer script:
>
> ```json
> "scripts": {
>     "clear-config-cache": "rm cache/config.php"
> }
> ```
>
> Allowing you to do this:
>
> ```bash
> $ composer clear-config-cache
> ```
>
> Which allows you to change the location of the cache file without needing to
> re-learn the location every time you need to clear the cache.

## Auto-enabling third-party providers

Being able to aggregate providers from third-parties is pretty stellar; it means
that you can be assured that configuration the third-party code expects is
generally present &mdash; with the exception of values that _must_ be provided by the
consumer, that is!

However, there's one minor problem: you need to remember to register these
configuration providers with your application, by manually editing your
`config.php` file and adding the appropriate entries.

Zend Framework solves this via the [zf-component-installer Composer
plugin](https://docs.zendframework.com/zend-component-installer/). If your
package is installable via Composer, you can add an entry to your package
definition as follows:

```json
"extra": {
    "zf": {
        "config-provider": [
            "Umbrella\\ConfigProvider"
        ]
    }
}
```

If the end-user:

- Has required `zendframework/zend-component-installer` in their application (as
  either a production or development dependency), **AND**
- has the config aggregation script in `config/config.php`

then the plugin will prompt you, asking if you would like to add each of the
`config-provider` entries found in the installed package into the configuration
script.

As such, for our example to work, we would need to move our configuration script
to `config/config.php`, and likely move our other configuration files into a
sub-directory:

```text
cache/
    config.php
config/
    config.php
    autoload/
        blanket.global.yaml
        global.php
        umbrella.global.json
```

This approach is essentially that taken by Expressive.

When those changes are made, any package you add to your application that
exposes configuration providers will prompt you to add them to your
configuration aggregation, and, if you confirm, will add them to the top of the
script!

## Final notes

First, we would like to thank [Mateusz Tymek](http://mateusztymek.pl/), whose
prototype 'expressive-config-manager' project became zend-config-aggregator.
This is a stellar example of a community project getting adopted into the
framework!

Second, this approach has some affinity to a proposal from the folks who brought
us PSR-11, which defines the `ContainerInterface` used within Expressive for
allowing usage of different dependency injection containers. That same group is
now working on a [service provider](https://github.com/container-interop/service-provider)
proposal that would standardize how standalone libraries expose services to
containers; we recommend looking at that project as well.

We hope that this post helps spawn ideas for configuring your next project!

> ## Save the date!
>
> Want to learn more about Expressive and Zend Framework? What better location
> than ZendCon 2017! ZendCon will be hosted 23-26 October 2017 in Las Vegas,
> Nevada, USA. [Visit the ZendCon website for more
> information](http://www.zendcon.com).
