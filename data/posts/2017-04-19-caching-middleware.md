---
layout: post
title: Caching middleware with Expressive
date: 2017-04-19T08:30:00-05:00
author: Enrico Zimuel
url_author: http://www.zimuel.it
permalink: /blog/2017-04-19-caching-middleware.html
categories:
- blog
- expressive

---

Performance is one of the key feature for web application. Using a middleware
architecture makes it very simple to implement a caching system in PHP.

The general idea is to store the response output of a URL in a file (or in
memory, using [memcached](https://memcached.org)) and use it for subsequent
requests. In this way we can bypass the execution of the previous middlewares
starting from the second request.

Of course, this technique can only be applied for static contents, that does not
require update for each HTTP request.

## Implement a caching middleware

Imagine we want to create a simple cache system with [Expressive](https://docs.zendframework.com/zend-expressive/).
We can use an implementation like that:

```php
namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

class CacheMiddleware implements ServerMiddlewareInterface
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $url  = str_replace('/', '_', $request->getUri()->getPath());
        $file = $this->config['path'] . $url . '.html';
        if ($this->config['enabled'] && file_exists($file) &&
            (time() - filemtime($file)) < $this->config['lifetime']) {
            return new HtmlResponse(file_get_contents($file));
        }

        $response = $delegate->process($request);
        if ($response instanceof HtmlResponse && $this->config['enabled']) {
            file_put_contents($file, $response->getBody());
        }
        return $response;
    }
}
```

In this example, we used the [PSR-15](https://github.com/php-fig/fig-standards/blob/master/proposed/http-middleware/middleware.md)
proposal to implement the Middleware interface using the `process()` function.
This is the suggested way to implement middleware in Expressive 2.0.

The idea of this middleware is quite simple. If the caching system is enabled
and if the requested URL matches an existing cache file, we return the cache
content as [HtmlResponse](https://zendframework.github.io/zend-diactoros/custom-responses/#html-responses),
ending the execution flow.

If the requested URL path does not exist in cache, we process the delegate
middleware (basically we continue with the normal workflow) and we store the
response in the cache, if enabled.

## Configure the cache system

To manage the cache, we used a configuration key `cache` to specify the `path` of
the cache files, the `lifetime` in seconds and the `enabled` value to turn on
and off the caching system.

Since we used a file to store the cache content, we can use the file
modification time to manage the lifetime of the cache. We used the [filemtime](http://php.net/manual/en/function.filemtime.php)
function of PHP to retrieve the modification file time.

> Note: if you want to use memcached instead of file you need to replace the
> `file_get_contents` and `file_put_contents` functions with `Memcached::get`
> and `Memcached::set`. Moreover, you do not need to check for lifetime
> because when you set a content in memcached you can specify the expiration
> directly.

In order to pass the `$config` dependency, we can use a simple factory class.
This is an example:

```php
namespace App\Action;

use Interop\Container\ContainerInterface;
use Exception;

class CacheFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        if (isset($config['cache']) && isset($config['cache']['enabled'])) {
            if ($config['cache']['enabled']) {
                if (!isset($config['cache']['path'])) {
                    throw new Exception('The cache path is not configured');
                }
                if (!isset($config['cache']['lifetime'])) {
                    throw new Exception('The cache lifetime is not configured');
                }
            }
        }
        return new CacheMiddleware($config['cache']);
    }
}
```
Following the folder structure of Expressive, we can store this configuration in
a simple PHP file in the `config/autoload` directory. For instance, we can store
it in `config/autoload/cache.local.php` file, as follows:

```php
return [
    'cache' => [
      'enabled'  => true,
      'path'     => __DIR__ . '/../../data/cache/',
      'lifetime' => 3600 // in seconds
    ]
];
```

We used the folder `/data/cache` for storing the caching file. The content of
this folder should be omitted in the version control. For instance, using git
you can omit the content putting a `.gitignore` file inside the `cache` folder
with the following content:

```php
*
!.gitignore
```

Finally, in order to activate the caching system we need to add the
`CacheMiddleware` class as service. In our example, we used [zend-servicemanager](https://github.com/zendframework/zend-servicemanager)
as service container. To add the cache system we can use a configuration file
(e.g. `/config/autoload/cache.global.php`) with the following content:

```php
return [
    'dependencies' => [
        'factories' => [
            App\Action\CacheMiddleware::class => App\Action\CacheFactory::class
        ]
    ]
];
```

## How enable the cache for specific routes

We mentioned early that this caching mechanism works fine for static content.
That means we need a way to enable the cache only for specific routes.

We can simply add the `CacheMiddleware` class as first middleware to be executed
for all the routes representing static contents.

For instance, imagine to have a `/about` route that show an about page of your
web site. We can add the `CacheMiddleware` as follow:

```php
use App\Action;

$app->get('/about', [
    Action\CacheMiddleware::class,
    Action\AboutAction::class
], 'about');
```

The middleware actions to be excuted for the `/about` URL are `CacheMiddleware`
and `AboutAction` in this order. The `$app` object is the instance of `Zend\Expressive\Application`,
the main class that manages the execution of an Expressive application.

## Conclusion

In this brief article we showed how to build a caching system for a PHP
application based on PSR-7 and PSR-15. A middleware architecture facilitates the
design of a cache layer because it uses the same workflow, an HTTP request as
input and an HTTP response as output. In this way, we can manage the HTTP
request, get the HTTP response for any middlewares and store the result for
caching, all in one place.

In this article we used the `zendframework/zend-expressive-skeleton` application
as example. For more information about Expressive, [visit the documentation](https://docs.zendframework.com/zend-expressive/).

> ## Save the date!
>
> Want to learn more about Expressive and Zend Framework? What better location
> than ZendCon 2017! ZendCon will be hosted 23-26 October 2017 in Las Vegas,
> Nevada, USA. [Visit the ZendCon website for more
> information](http://www.zendcon.com).
