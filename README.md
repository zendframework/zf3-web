# Zend Framework 3 web site

This is the prototype version of the new Zend Framework web site.

This web site is realized with [zend-expressive](https://github.com/zendframework/zend-expressive)
running on PHP 7.

## Getting Started

Install all the dependency using [composer](https://getcomposer.org/):

```bash
$ composer install
```

After you can run the site using the PHP internal web server:

```bash
$ composer serve
```

You can then browse to http://localhost:8080.


## TO DO

- [ ] issues pages on /issues/browser/:code, where code is in the format ZF-number
- [ ] reference guide for ZF1 and ZF2, based on the [existing one](http://framework.zend.com/manual/current/en/index.html)
- [ ] new manual page for ZF3, basically a static page with links to all the docs of the specific components
- [ ] download page, with the list of all the components
- [ ] participate page based on the [existing one](http://framework.zend.com/participate/)
- [ ] /security/feed as RSS
- [ ] subscribe to the blog via RSS
- [ ] English review of all the pages
