# Zend Framework 3 web site

[![Build Status](https://secure.travis-ci.org/ezimuel/zf3-web.svg?branch=master)](https://secure.travis-ci.org/ezimuel/zf3-web)

This is the prototype version of the new Zend Framework web site.

This web site is realized with [zend-expressive](https://github.com/zendframework/zend-expressive)
running on PHP 7.

## Getting Started

Install all the dependency using [composer](https://getcomposer.org/):

```bash
$ composer install
```

After you need to build the configuration and cache files of the web site using
the following command:

```bash
php bin/build.php
```

After you need to configure the `config/autoload/local.php` file:

```bash
cp config/autoload/local.php.dist config/autoload/local.php
```

Edit the local.php file and fill the fields with the right values:

```php
return [
    'debug' => false,
    'zf_manual_basepath' => '<path to ZF manuals folder>',
    'config_cache_enabled' => false
];
```
If you set true the `config_cache_enabled` remember to configure the ENV
variable `APP_CACHE` in your webserver. For instance, using Nginx:

```
server {
  ...
  fastcgi_param APP_CACHE "<path to cache folder>";
  ...
}
```

Finally you can execute the web site, for instance using the PHP web server:

```bash
$ composer serve
```

You can then browse to http://localhost:8080.

## Stats and statistics pages

The web site offers a statistics and status page. In order to generate the
statistics data we used the  [Packagist API](https://packagist.org/apidoc).

You need to execute the following command to generate it:

```bash
php bin/stats.php
```

## Update the website content

If you want to add or update content of the website related to blog, security
advisories, issues or changelog, you need to re-build the configuration files
using the following command:

```bash
php bin/build.php
```

To update the statistics you should schedule the execution of the `bin/stats.php`
script. For instance you can use [Cron](https://en.wikipedia.org/wiki/Cron) on a
GNU/Linux environment and schedule the execution each hour.
