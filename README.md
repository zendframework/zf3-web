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

## Cache management

The first time that you open the web site you will notice a response delay. This
is due to the cache generation files under the `/data/cache` folder.

This will occur only for the first request, the application will reuse the
generated files from the second request.

## Update the website content

If you want to add or update content of the website related to blog, security
advisories or changelog, you need to remove the cache files under the
`/data/cache` folder. These files are auto-generated during the first HTTP
request.


## TO DO

- [x] issues pages on /issues/browser/:code, where code is in the format ZF-number
- [ ] reference guide for ZF1 and ZF2, based on the [existing one](http://framework.zend.com/manual/current/en/index.html)
- [ ] new manual page for ZF3, basically a static page with links to all the docs of the specific components
- [ ] download page, with the list of all the components
- [ ] participate page based on the [existing one](http://framework.zend.com/participate/)
- [ ] /security/feed as RSS
- [ ] subscribe to the blog via RSS
- [ ] English text review of all the pages
