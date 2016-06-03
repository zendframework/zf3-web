# Zend Framework 3 web site

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

After that you can execute the web site, for instance using the PHP web server:

```bash
$ composer serve
```

You can then browse to http://localhost:8080.

## Update the website content

If you want to add or update content of the website related to blog, security
advisories, issues or changelog, you need to re-build the configuration files
using the following command:

```bash
php bin/build.php
```
