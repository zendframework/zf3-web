---
layout: post
title: zend-config For All Your Configuration Needs
date: 2017-02-22T10:10:00-06:00
author: Matthew Weier O'Phinney
url_author: https://mwop.net/
permalink: /blog/2017-02-22-zend-config.html
categories:
- blog
- components
- zend-config

---

Different applications and frameworks have different opinions about how
configuration should be created. Some prefer XML, others YAML, some like JSON,
others like INI, and some even stick to the JavaProperties format; in Zend
Framework, we tend to prefer PHP arrays, as each of the other formats
essentially get compiled to PHP arrays eventually anyways.

At heart, though, we like to support developer needs, whatever they may be, and,
as such, our [zend-config component](https://docs.zendframework.com/zend-config/)
provides ways of working with a variety of configuration formats.

## Installation

zend-config is installable via Composer:

```bash
$ composer require zendframework/zend-config
```

The component has two dependencies:

- [zend-stdlib](https://docs.zendframework.com/zend-stdlib/), which provides some
  capabilities around configuration merging.
- [psr/container](https://github.com/php-fig/container), to allow reader and
  writer plugin support for the configuration factory.

> ### Latest version
>
> This article covers the most recently released version of zend-config, 3.1.0,
> which contains a number of features such as PSR-11 support that were not
> previously available. If you are using Zend Framework, you should be able to
> safely provide the constraint `^2.6 || ^3.1`, as the primary APIs remain the
> same.

## Retrieving configuration

Once you've installed zend-config, you can start using it to retrieve and access
configuration files. The simplest way is to use `Zend\Config\Factory`, which
provides tools for loading configuration from a variety of formats, as well as
capabilities for merging.

If you're just pulling in a single file, use `Factory::fromFile()`:

```php
use Zend\Config\Factory;

$config = Factory::fromFile($path);
```

Far more interesting is to use multiple files, which you can do via
`Factory::fromFiles()`. When you do, they are merged into a single
configuration, in the order in which they are provided to the factory. This is
particularly interesting using `glob()`:

```php
use Zend\Config\Factory;

$config = Factory::fromFiles(glob('config/autoload/*.*'));
```

What's particularly interesting about this is that it supports a variety of
formats:

- PHP files returning arrays (`.php` extension)
- INI files (`.ini` extension)
- JSON files (`.json` extension)
- XML files (using PHP's `XMLReader`; `.xml` extension)
- YAML files (using ext/yaml, installable via PECL; `.yaml` extension)
- JavaProperties files (`.javaproperties` extension)

This means that you can choose the configuration format you prefer, or
mix-and-match multiple formats, if you need to combine configuration from
multiple libraries!

## Configuration objects

By default, `Zend\Config\Factory` will return PHP arrays for the merged
configuration. Some dependency injection containers do not support arrays as
services, however; moreover, you may want to pass some sort of structured object
instead of a plain array when injecting dependencies.

As such, you can pass a second, optional argument to each of `fromFile()` and
`fromFiles()`, a boolean flag. When `true`, it will return a
`Zend\Config\Config` instance, which implements `Countable`, `Iterator`, and
`ArrayAccess`, allowing it to look and act like an array.

What is the benefit?

First, it provides property overloading to each configuration key:

```php
$debug = $config->debug ?: false;
```

Second, it offers a convenience method, `get()`, which allows you to specify a
default value to return if the value is not found:

```php
$debug = $config->get('debug', false); // Return false if not found
```

This is largely obviated by the `?:` ternary shortcut in modern PHP versions,
but very useful when _mocking_ in your tests.

Third, nested sets are also returned as `Config` instances, which gives you the
ability to use the above `get()` method on a nested item:

```php
if (isset($config->expressive)) {
    $config = $config->get('expressive'); // same API!
}
```

Fourth, you can mark the `Config` instance as immutable! By default, it acts
just like array configuration, which is, of course, mutable. However, this can
be problematic when you use configuration as a service, because, unlike an
array, a `Config` instance is passed by reference, and changes to values would
then propagate to any other services that depend on the configuration.

Ideally, you wouldn't be changing any values in the instance, but
`Zend\Config\Config` can enforce that for you:

```php
$config->setReadOnly(); // Now immutable!
```

Further, calling this will mark nested `Config` instances as read-only as well,
ensuring data integrity for the entire configuration tree.

> ### Read-only by default!
>
> One thing to note: by default, `Config` instances are read-only! The
> constructor accepts an optional, second argument, a flag indicating whether or
> not the instance allows modifications, and the value is `false` by default.
> Whenever you use the `Factory` to create a `Config` instance, it never enables
> that flag, meaning that if you return a `Config` instance, it will be read-only.
> 
> If you want a mutable instance from a `Factory`, use the following construct:
> 
> ```php
> use Zend\Config\Config;
> use Zend\Config\Factory;
> 
> $config = new Config(Factory::fromFiles($files), true);
> ```

## Including other configuration

Most of the configuration reader plugins also support "includes": directives
within a configuration file that will include configuration from another file.
(JavaProperties is the only configuration format we support that does not have
this functionality included.)

For instance:

- INI files can use the key `@include` to include another file relative to the
  current one; values are merged at the same level:

  ```ini
  webhost = 'www.example.com'
  @include = 'database.ini'
  ```

- For XML files, you can use [XInclude](http://www.w3.org/TR/xinclude/):

  ```xml
  <?xml version="1.0" encoding="utf-8">
  <config xmlns:xi="http://www.w3.org/2001/XInclude">
    <webhost>www.example.com</webhost>
    <xi:include href="database.xml"/>
  </config>
  ```

- JSON files can use an `@include` key:

  ```json
  {
    "webhost": "www.example.com",
    "@include": "database.json"
  }
  ```

- YAML also uses the `@include` notation:

  ```yaml
  webhost: www.example.com
  @include: database.yaml
  ```

## Choose your own YAML

Out-of-the-box we support the [YAML PECL extension](http://www.php.net/manual/en/book.yaml.php)
for our YAML support. However, we have made it possible to use alternate
parsers, such as Spyc or the Symfony YAML component, by passing a callback to the
reader's constructor:

```php
use Symfony\Component\Yaml\Yaml as SymfonyYaml;
use Zend\Config\Reader\Yaml as YamlConfig;

$reader = new YamlConfig([SymfonfyYaml::class, 'parse']);
$config = $reader->fromFile('config.yaml');
```

Of course, if you're going to do that, you could just use the original library,
right? But what if you want to mix YAML and other configuration with the
`Factory` class?

There aer two ways to register new plugins. One is to create an instance and
register it with the factory:

```php
use Symfony\Component\Yaml\Yaml as SymfonyYaml;
use Zend\Config\Factory;
use Zend\Config\Reader\Yaml as YamlConfig;

Factory::registerReader('yaml', new YamlConfig([SymfonyYaml::class, 'parse']));
```

Alternately, you can provide an alternate reader plugin manager. You can do that
by extending `Zend\Config\StandaloneReaderPluginManager`, which is a barebones
PSR-11 container for use as a plugin manager:

```php
namespace Acme;

use Symfony\Component\Yaml\Yaml as SymfonyYaml;
use Zend\Config\Reader\Yaml as YamlConfig;
use Zend\Config\StandaloneReaderPluginManager;

class ReaderPluginManager extends StandaloneReaderPluginManager
{
    /**
     * @inheritDoc
     */
    public function has($plugin)
    {
        if (YamlConfig::class === $plugin
            || 'yaml' === strtolower($plugin)
        ) {
            return true;
        }

        return parent::has($plugin);
    }

    /**
     * @inheritDoc
     */
    public function get($plugin)
    {
        if (YamlConfig::class !== $plugin
            && 'yaml' !== strtolower($plugin)
        ) {
            return parent::get($plugin);
        }

        return new YamlConfig([SymfonyYaml::class, 'parse']);
    }
}
```

Then register this with the `Factory`:

```php
use Acme\ReaderPluginManager;
use Zend\Config\Factory;

Factory::setReaderPluginManager(new ReaderPluginManager());
```

## Processing configuration

zend-config also allows you to _process_ a `Zend\Config\Config` instance and/or
an individual value. Processors perform operations such as:

- substituting constant values within strings
- filtering configuration data
- replacing tokens within configuration
- translating configuration values

Why would you want to do any of these operations?

Consider this: deserialization of formats other than PHP cannot take into
account PHP constant values or class names!

While this may work in PHP:

```php
return [
    Acme\Component::CONFIG_KEY => [
        'host' => Acme\Component::CONFIG_HOST,
        'dependencies' => [
            'factories' => [
                Acme\Middleware\Authorization::class => Acme\Middleware\AuthorizationFactory::class,
            ],
        ],
    ],
];
```

The following JSON configuration would not:

```json
{
    "Acme\\Component::CONFIG_KEY": {
        "host": "Acme\\Component::CONFIG_HOST"
        "dependencies": {
            "factories": {
                "Acme\\Middleware\\Authorization::class": "Acme\\Middleware\\AuthorizationFactory::class"
            }
        }
    }
}
```

Enter the `Constant` processor!

This processor looks for strings that match constant names, and replaces them
with their values. Processors generally only work on the configuration _values_,
but the `Constant` processor allows you to opt-in to processing the _keys_ as
well.

Since processing _modifies_ the `Config` instance, you will need to manually
create an instance, and then process it. Let's look at that:

```php
use Acme\Component;
use Zend\Config\Config;
use Zend\Config\Factory;
use Zend\Config\Processor;

$config = new Config(Factory::fromFile('config.json'), true);
$processor = new Processor\Constant();
$processor->enableKeyProcessing();
$processor->process($config);
$config->setReadOnly();

var_export($config->{Component::CONFIG_KEY}->dependencies->factories);
// ['Acme\Middleware\Authorization' => 'Acme\Middleware\AuthorizationFactory']
```

This is a really powerful feature, as it allows you to add more verifications
and validations to your configuration files, regardless of the format you use.

> ### In version 3.1.0 forward
>
> The ability to work with class constants and process keys was added only
> recently in the 3.1.0 version of zend-config.

## Config all the things!

This post covers the parsing features of zend-config, but does not even touch on
another major capability: the ability to _write_ configuration! We'll leave that
to another post.

In terms of configuration parsing, zend-config is simple, yet powerful. The
ability to process a number of common configuration formats, utilize
configuration includes, and process keys and values means you can highly
customize your configuration process to suit your needs or integrate different
configuration sources.

Get more information from the [zend-config
documentation](https://docs.zendframework.com/zend-config/).
