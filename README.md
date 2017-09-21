# Zend Framework web site

[![Build Status](https://secure.travis-ci.org/zendframework/zf3-web.svg?branch=master)](https://secure.travis-ci.org/zendframework/zf3-web)

This web site is created with [zend-expressive](https://github.com/zendframework/zend-expressive)
running on PHP 7.

## Getting Started

Install dependencies using [composer](https://getcomposer.org/):

```bash
$ composer install
```

First, update the `config/autoload/local.php` configuration file:

```bash
$ cp config/autoload/local.php.dist config/autoload/local.php
```

Edit the `config/autoload/local.php` file and fill the fields with appropriate
values:

```php
return [
    'debug' => false,
    'zf_manual_basepath' => '<path to ZF manuals folder>',
    'config_cache_enabled' => false,
];
```

If you enable `config_cache_enabled`, you will need to configure the `ENV`
variable `APP_CACHE` in the file `.docker/nginx/default.conf`:

```nginx
server {
  ...
  fastcgi_param APP_CACHE "<path to cache folder>";
  ...
}
```

Afterwards, you need to build the configuration and cache files of the web site
using the following command:

```bash
$ php bin/build.php
```

## Docker

For development, we use [docker-compose](https://docs.docker.com/compose/);
make sure you have both that and Docker installed on your machine.

Build the images:

```bash
$ docker-compose build
```

And then launch them:

```bash
$ docker-compose up -d
```

You can then browse to `http://localhost:8080`, and any changes you make in the
project will be reflected immediately.

## Stats and statistics pages

The web site offers a statistics and status page. In order to generate the
statistics data, we use the [Packagist API](https://packagist.org/apidoc).

You need to execute the following command to generate it:

```bash
$ php bin/stats.php <path-to-stat-file>
```

This script will save the statistics number in the `<path-to-stat-file>` and
create a symlink to it in the `config/autoload/zf-stats.local.php` file.

When you update the stats file with config caching enabled, you need to
delete the cache file to get the new statistics numbers. In order to faciliate
this process, we provide a [node.js](https://nodejs.org/en/) script,
`bin/watch.js`, that removes the cache file on each change of the file stat.

You can execute the `watch.js` script using the following command:

```bash
$ node watch.js <file-to-watch> <file-cache-to-remove>
```

where `<file-to-watch>` is the stat file to watch and `<file-cache-to-remove>`
is the file cache to remove.

This script can be easily configured as a service in GNU/Linux environments
using [Systemd](https://en.wikipedia.org/wiki/Systemd).

We provide a `bin/statswatch.system` configuration file to be used to execute
the script as a service. First, copy `bin/statswatch.system` to your
`/etc/systemd/system` directory:

```bash
$ sudo cp bin/statswatch.system /etc/systemd/system
```

Once that is done, refresh the systemd daemon and start the statswatch service:

```bash
$ sudo systemctl daemon-reload
$ sudo systemctl start statswatch
```

To watch logs for `statswatch` in realtime:

```bash
$ sudo journalctl --follow -u statswatch
```

To start the service during server boot:

```bash
$ sudo systemctl enable statswatch
```

## Update the website content

Any time you add or update content of the website related to the blog, security
advisories, issues, or changelogs, you need to re-build the configuration files
using the following command:

```bash
$ php bin/build.php
```

If you have setup a configuration cache directory, define the `APP_CACHE`
environment variable before invoking the script:

```bash
$ APP_CACHE=/path/to/cache/ php bin/build.php
```

## CSS and JavaScript

CSS can be found in the `asset/sass/` directory (we use SASS for defining our CSS),
and JS can be found in the `asset/js/` directory.

After changing CSS or JS you need rebuild assets, as following:
```bash
$ cd asset
$ npm install
$ gulp
```

New files will be generated in `public/js` and `public/css` and old files will
be removed. File `asset/rev-manifest.json` will contain new revision names for
our assets. The file is used by [`asset` view helper](https://docs.zendframework.com/zend-view/helpers/asset/).
