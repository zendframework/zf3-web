# Zend Framework 3 web site

This is the prototype version of the new Zend Framework web site.

This web site is realized with [zend-expressive](https://github.com/zendframework/zend-expressive)
running on PHP 7.

## Getting Started

Install all the dependency using [composer](https://getcomposer.org/):

```bash
$ composer install
```

After you need to build the cache files of the web site using the following
command:

```bash
php bin/build_cache.php
```

This command will create the cache files under the folder `/data/cache`.
After that you can execute the web site, for instance using the PHP web server:

```bash
$ composer serve
```

You can then browse to http://localhost:8080.

## Update the website content

If you want to add or update content of the website related to blog, security
advisories, issues or changelog, you need to re-build the cache files using
the following command:

```bash
php bin/build_cache.php
```

## TO DO

- [x] issues pages on /issues/browser/:code, where code is in the format ZF-number
- [x] reference guide for ZF1 and ZF2, based on the [existing one](http://framework.zend.com/manual/current/en/index.html)
- [x] new manual page for ZF3, basically a static page with links to all the docs of the specific components
- [ ] download page, with the list of all the components
- [ ] participate page based on the [existing one](http://framework.zend.com/participate/)
- [ ] /security/feed as RSS
- [ ] subscribe to the blog via RSS
- [ ] English text review of all the pages
