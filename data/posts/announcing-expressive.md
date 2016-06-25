---
layout: post
title: Announcing Expressive
date: 2015-08-26 20:25
update: 2015-08-26 20:25
author: Matthew Weier O'Phinney
url_author: http://mwop.net/
permalink: /blog/announcing-expressive.html
categories:
- blog
- expressive

---

 We are pleased to announce the immediate availability of a new project, [Expressive](https://github.com/zendframework/zend-expressive)!

 Expressive allows you to write [PSR-7](http://www.php-fig.org/psr/psr-7/) [middleware](https://github.com/zendframework/zend-stratigility/blob/master/doc/book/middleware.md) applications for the web. It is a simple micro-framework built on top of [Stratigility](https://github.com/zendframework/zend-stratigility), providing:

- Dynamic routing
- Dependency injection via container-interop
- Templating
- Error Handling

Installation and Quick Start
----------------------------

Expressive can get you up and running with an application in minutes.

To install, use [Composer](https://getcomposer.org):

 ```bash
$ composer require zendframework/zend-expressive aura/router zendframework/zend-servicemanager
```

From there to "hello, world,", all you now need is a single file:

```php 
// In index.php
use Zend\Expressive\AppFactory;

require 'vendor/autoload.php';

$app = AppFactory::create();
$app->route('/', function ($request, $response, $next) {
    $response->getBody()->write('Hello, world!');
    return $response;
});
$app->run();
```
From there, fire up the built-in web server:

```bash 
$ php -S 0.0.0.0:8080 -t .
```

Browse to localhost:8080, and you should see it running!

Visit [our documentation for the full quick start](http://zend-expressive.readthedocs.org/en/stable/quick-start/).

Breaking out of the box
-----------------------

A huge part of the PHP Renaissance has been due to the advent of [Composer](https://getcomposer.org), which has simplified dependency management, and led to tens of thousands of standalone libraries and packages. As such, frameworks, while still popular, are often being eschewed for homemade, application-specific frameworks made of commodity components. Frameworks simply cannot ignore this trend, and decoupling should become the norm going forward.

With [Apigility](https://apigility.org), the Zend Framework team began using third party software as part of the solutions it provides. With Expressive, we took that even further: we provide abstractions for routing and templating capabilities, but largely rely on third-party libraries for the recommended implementations.

Expressive features integrations with:

- Aura.Router
- FastRoute
- Pimple
- Plates
- Twig

as well as related Zend Framework components. In most cases, integrations were developed for third party libraries _before_ we wrote integrations with Zend Framework components!

As such, Expressive is a small, single-purpose component that can integrate other components to create a custom middleware runtime for your applications.

Where does this fit with Zend Framework?
----------------------------------------

We feel that PSR-7 opens new paradigms for both interoperability as well as for application design. Middleware offers a pattern for re-use and composability that is often far simpler to understand, and which often allows building complex applications from smaller pieces. As such, we want to provide an easy way to build middleware-based applications _now_.

We will, however, continue to ship Zend Framework and its full-stack MVC. Many complex applications can benefit from the highly flexible structure it provides, and we plan to continue supporting those users well into the future. We also plan to add capabilities (quite soon!) for executing PSR-7 based middleware from within Zend Framework applications; this provides migration paths in both directions for developers.

More Information and Roadmap
----------------------------

Expressive is open source software, and we're trying to follow the mantra of "release early, release often." As such, our initial stable tag is at 0.1.0, and we're requesting that you start playing with it and letting us know what works and what doesn't. You can report issues on the [issue tracker](https://github.com/zendframework/zend-expressive/issues).

One big push for us has been to document everything we can; you can currently [browse our documentation on ReadTheDocs](http://zend-expressive.readthedocs.org/en/stable/). If you have questions, changes, or additions you feel should be made, documentation is part of the code repository itself, and issues can be raised just as they can for code.

While this is an initial offering, we've put a lot of thought into the various features and abstractions, and feel it is essentially feature complete. We do, however, have a bucket list of additional features we wish to support before we go stable:

- A skeleton application. Ideally, we would like Composer hooks that can ask which implementations for routing, container, and/or templating are desired. If you know how to do this, we'd love your help!
- Session encryption.
- HTTP Caching support.
- User authentication (via OAuth2 and/or other social auth mechanisms).

Additionally, in the coming weeks, we'll be expanding our [PSR-7 <-> zend-http bridge](https://github.com/zendframework/zend-psr7bridge), and creating an alternate, PSR-7 middleware dispatcher that can be used with the ZF2 MVC.

We welcome any assistance you as contributors can offer in these initiatives!
