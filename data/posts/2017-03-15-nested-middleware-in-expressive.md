---
layout: post
title: Nested Middleware in Expressive
date: 2017-03-15T15:00:00-05:00
updated: 2017-03-15T16:02:00-05:00
author: Matthew Weier O'Phinney
url_author: https://mwop.net/
permalink: /blog/2017-03-15-nested-middleware-in-expressive.html
categories:
- blog
- expressive
- psr-7
- psr-15

---

A major reason to adopt a middleware architecture is the ability to create
custom workflows for your application. Most traditional MVC architectures have a
very specific workflow the request follows. While this is often customizable via
event listeners, the events and general request lifecycle is the same for each
and every resource the application serves.

With middleware, however, you can define your own workflow by composing
middleware.

## Expressive pipelines

In Expressive, we call the workflow the application _pipeline_, and you create
it by _piping_ middleware into the application. As an example, the default
pipeline installed with the skeleton application looks like this:

```php
// In config/pipeline.php:
use Zend\Expressive\Helper\ServerUrlMiddleware;
use Zend\Expressive\Helper\UrlHelperMiddleware;
use Zend\Expressive\Middleware\ImplicitHeadMiddleware;
use Zend\Expressive\Middleware\ImplicitOptionsMiddleware;
use Zend\Expressive\Middleware\NotFoundHandler;
use Zend\Stratigility\Middleware\ErrorHandler;

$app->pipe(ErrorHandler::class);
$app->pipe(ServerUrlMiddleware::class);
$app->pipeRoutingMiddleware();
$app->pipe(ImplicitHeadMiddleware::class);
$app->pipe(ImplicitOptionsMiddleware::class);
$app->pipe(UrlHelperMiddleware::class);
$app->pipeDispatchMiddleware();
$app->pipe(NotFoundHandler::class);
```

In this particular workflow, what happens when a request is processed is the
following:

- The `ErrorHandler` middleware (which handles exceptions and PHP errors) is
  processed, which in turn:
  - processes the `ServerUrlMiddleware` (which injects the request URI into the
    `ServerUrl` helper), which in turn:
    - process the routing middleware, which in turn:
      - process the `ImplicitHeadMiddleware` (which provides responses for
        `HEAD` requests if the matched middleware does not handle that method),
        which in turn:
        - processes the `ImplicitOptionsMiddleware` (which provides responses
          for `OPTIONS` requests if the matched middleware does not handle that
          method), which in turn:
          - processes the `UrlHelperMiddleware` (which injects the `UrlHelper`
            with the `RouteResult` from routing, if discovered), which in turn:
            - processes the dispatch middleware, which in turn:
              - processes the matched middleware, if present
              - processes the `NotFoundHandler`, if no middleware was matched by
                routing, or that middleware cannot handle the request.

At any point in the workflow, middleware can choose to return a response. For
instance, the `ImplicitHeadMiddleware` and `ImplicitOptionsMiddleware` may
return a response if the middleware matched by routing cannot handle the
specified method. When they do, no layers below are executed!

## Scenario: Adding Authentication

Now, let's say we want to add authentication to our application.

For purposes of this example, we'll use the [Digest authentication
middleware](https://github.com/oscarotero/psr7-middlewares#digestauthentication)
from the [oscarotero/psr7-middlewares package](https://github.com/oscarotero/psr7-middlewares):

```bash
$ composer require oscarotero/psr7-middlewares
```

First, we'll build middleware that will accept a list of credentials in order to
build the authentication mechanism, and then, when processed, authenticate the
request:

```php
<?php
namespace Acme;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\ResponseInterface;
use Psr\Http\ServerRequestInterface;
use Psr7Middlewares\Middleware\DigestAuthentication;
use Zend\Diactoros\Response;

class AuthenticationMiddleware implements MiddlewareInterface
{
    /**
     * @var array
     */
    private $credentials;

    /**
     * @var string
     */
    private $realm;

    /**
     * @var array $credentials Username/password pairs
     * @var string $realm Realm for authentication
     */
    public function __construct(array $credentials, $realm = 'Acme')
    {
        $this->credentials = $credentials;
        $this->realm = $realm;
    }

    /**
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $authentication = new DigestAuthentication($this->credentials);
        $authentication->realm($this->realm);
        $authentication->nonce(uniqid());

        return $authentication(
            $request,
            new Response(),
            function ($request) use ($delegate) {
                return $delegate->process($request);
            }
        );
    }
}
```

(The above adapts the `DigestAuthentication` middleware to work with
http-interop.)

This middleware returns a 401 response if login fails, which is exactly what we
want.

Now we'll create a factory:

```php
<?php
namespace Acme;

use Psr\Container\ContainerInterface;

class AuthenticationMiddlewareFactory
{
    /**
     * @return AuthenticationMiddleware
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $credentials = $config['authentication']['credentials'] ?? [];
        $realm = $config['authentication']['realm'] ?? __NAMESPACE__;

        return new AuthenticationMiddleware($credentials, $realm);
    }
}
```

Wire this in your dependencies somewhere; we recommend either the file
`config/autoload/dependencies.global.php` or the class `Acme\ConfigProvider` if
you have defined it:

```php
'dependencies' => [
    'factories' => [
        Acme\AuthenticationMiddleware::class => Acme\AuthenticationMiddlewareFactory::class,
    ],
],
```

Now, we'll add this to the pipeline.

If you want _every_ request to require authentication, you can pipe this in
early, sometime after the `ErrorHandler` and any middleware you want to run for
every request:

```php
// In config/pipeline.php:

$app->pipe(ErrorHandler::class);
$app->pipe(ServerUrlMiddleware::class);
$app->pipe(\Acme\AuthenticationMiddleware::class);
```

Done!

But... this means that _all_ pages of the application now require
authentication! You likely don't want to require authentication for the home
page, and potentially many others.

Let's look at some options.

## Segregating by path

One option available in Expressive is _path segregation_. If you know every
route requiring authentication will have the same path prefix, you can use this
approach.

As an example, let's say you only want authentication for your API, and all API
paths fall under the path prefix `/api`. This means you could do the following:

```php
$app->pipe('/api', \Acme\AuthenticationMiddleware::class);
```

This middleware will only execute if the request path matches `/api`.

But what if you only really need authentication for _specific routes_ under the
API?

## Nested middleware

We finally get to the purpose of this tutorial!

Let's say our API defines the following routes:

```php
// In config/routes.php:

$app->get('/api/books', Acme\Api\BookListMiddleware::class, 'api.books');
$app->post('/api/books', Acme\Api\CreateBookMiddleware::class);
$app->get('/api/books/{book_id:\d+}', Acme\Api\BookMiddleware::class, 'api.book');
$app->patch('/api/books/{book_id:\d+}', Acme\Api\UpdateBookMiddleware::class);
$app->delete('/api/books/{book_id:\d+}', Acme\Api\DeleteBookMiddleware::class);
```

In this scenario, we want to require authentication only for the
`CreateBookMiddleware`, `UpdateBookMiddleware`, and `DeleteBookMiddleware`. How
do we do that?

Expressive allows you to provide a _list_ of middleware both when piping and
routing, instead of a single middleware. Just as when you specify a single
middleware, each entry may be one of:

- callable middleware
- middleware instance
- service name resolving to middleware

Internally, Expressive creates a `Zend\Stratigility\MiddlewarePipe` instance
with the specified middleware, and processes this pipeline when the given
middleware is matched.

So, going back to our previous example, where we defined routes, we can rewrite
them as follows:

```php
// In config/routes.php:

$app->get('/api/books', Acme\Api\BookListMiddleware::class, 'api.books');
$app->post('/api/books', [
    Acme\AuthenticationMiddleware::class,
    Acme\Api\CreateBookMiddleware::class,
]);
$app->get('/api/books/{book_id:\d+}', Acme\Api\BookMiddleware::class, 'api.book');
$app->patch('/api/books/{book_id:\d+}', [
    Acme\AuthenticationMiddleware::class,
    Acme\Api\UpdateBookMiddleware::class,
]);
$app->delete('/api/books/{book_id:\d+}', [
    Acme\AuthenticationMiddleware::class,
    Acme\Api\DeleteBookMiddleware::class,
]);
```

In this particular case, this means that the `AuthenticationMiddleware` will
only execute for one of the following:

- `POST` requests to `/api/books`
- `PATCH` requests to `/api/books/123` (or any valid identifier)
- `DELETE` requests to `/api/books/123` (or any valid identifier)

In each case, if authentication fails, the later middleware in the list _will
not be processed_, as the `AuthenticationMiddleware` will return a 401 response.

This technique allows for some powerful workflows. For instance, when creating a
book via the `/api/books` middleware, we could also add in middleware to check
the content type, parse the incoming request, and validate the submitted data:

```php
// In config/routes.php:

$app->post('/api/books', [
    Acme\AuthenticationMiddleware::class,
    Acme\ContentNegotiationMiddleware::class,
    Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware::class,
    Acme\Api\BookValidationMiddleware::class,
    Acme\Api\CreateBookMiddleware::class,
]);
```

(We leave implementation of most of the above middleware as an exercise for the
reader!)

By using service names, you also ensure that optimal performance; the middleware
will not be instantiated unless the request matches, and the middleware is
executed. In fact, if one of the pipeline middleware for the given route returns
a response early, even the middleware later in the queue will not be
instantiated!

> ### A note about order
>
> When you create middleware pipelines such as the above, as well as in the
> following examples, _order matters_. Pipelines are managed internally as
> queues, and thus are first-in-first-out (FIFO). As such, putting the
> responding `CreateBookMiddleware` (which will most likely return a response
> with the API payload) will result in the other middleware never executing!
>
> As such, ensure that your pipelines contain middleware that will _delegate_
> first, and your primary middleware that returns a response last.

## Middleware pipelines

Another approach would be to setup a middleware pipeline manually within the
factory for the requested middleware. The following examples creates and
returns a `Zend\Stratigility\MiddlewarePipe` instance that composes the same
middleware as in the previous example that used a list of middleware when
routing, returning the `MiddlewarePipe` instead of the requested
`CreateBookMiddleware` (but composing it nonetheless):

```php
namespace Acme\Api;

use Acme\AuthenticationMiddleware;
use Acme\ContentNegotiationMiddleware;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware;
use Zend\Stratigility\MiddlewarePipe;

class CreateBookMiddlewareFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $pipeline = new MiddlewarePipe();

        $pipeline->pipe($container->get(AuthenticationMiddleware::class));
        $pipeline->pipe($container->get(ContentValidationMiddleware::class));
        $pipeline->pipe($container->get(BodyParamsMiddleware::class));
        $pipeline->pipe($container->get(BookValidationMiddleware::class));
        $pipeline->pipe($container->get(CreateBookMiddleware::class));

        return $pipeline;
    }
}
```

This approach is inferior to using an array of middleware, however. Internally,
Expressive will wrap the various middleware services you list in
`LazyLoadingMiddleware` instances; this means that if a service earlier in the
pipeline returns early, the service will never be pulled from the container.
This can be important if any services might establish network connections or
perform file operations during initialization!

## Nested applications

Since Expressive does the work of lazy loading services, another option would be
to create another Expressive `Application` instance, and feed it, instead of
creating a `MiddlewarePipe`:

```php
namespace Acme\Api;

use Acme\AuthenticationMiddleware;
use Acme\ContentNegotiationMiddleware;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Application;
use Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware;
use Zend\Expressive\Router\RouterInterface;

class CreateBookMiddlewareFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $nested = new Application(
          $container->get(RouterInterface::class),
          $container
        );

        $nested->pipe(AuthenticationMiddleware::class);
        $nested->pipe(ContentValidationMiddleware::class);
        $nested->pipe(BodyParamsMiddleware::class);
        $nested->pipe(BookValidationMiddleware::class);
        $nested->pipe(CreateBookMiddleware::class);

        return $nested;
    }
}
```

The benefit this approach has is that you get the lazy-loading middleware
instances without effort. However, it makes discovery of what the middleware
consists more difficult &mdash; you can't just look at the routes anymore, but
need to look at the factory itself to see what the workflow looks like. When you
consider re-distribution and re-use, though, this approach has a lot to offer,
as it combines the performance of defining an application pipeline with the
ability to re-use that same workflow any time you use that particular
middleware in an application.

(The above could even use separate router and container instances entirely, in
order to keep the services and routing for the middleware pipeline completely
separate from those of the main application!)

## Using traits for common workflows

The above approach of creating a nested application, as well as the original
example of nested middleware provided via arrays, has one drawback: if several
middleware need the exact same workflow, you'll have repetition.

One approach is to create a [trait](http://php.net/trait) for creating the
`Application` instance and populating the initial pipeline.

```php
namespace Acme\Api;

use Acme\AuthenticationMiddleware;
use Acme\ContentNegotiationMiddleware;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Application;
use Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware;
use Zend\Expressive\Router\RouterInterface;

trait CommonApiPipelineTrait
{
    private function createNestedApplication(ContainerInterface $container)
    {
        $nested = new Application(
          $container->get(RouterInterface::class),
          $container
        );

        $nested->pipe(AuthenticationMiddleware::class);
        $nested->pipe(ContentValidationMiddleware::class);
        $nested->pipe(BodyParamsMiddleware::class);
        $nested->pipe(BookValidationMiddleware::class);

        return $nested;
    }
}
```

Our `CreateBookMiddlewareFactory` then becomes:

```php
namespace Acme\Api;

use Psr\Container\ContainerInterface;

class CreateBookMiddlewareFactory
{
    use CommonApiPipelineTrait;

    public function __invoke(ContainerInterface $container)
    {
        $nested = $this->createNestedApplication($container);
        $nested->pipe(CreateBookMiddleware::class);
        return $nested;
    }
}
```

Any middleware that would need the same workflow can now provide a factory that
uses the same trait. This, of course, means that the factories for any given
middleware that adopts the specific workflow reflect that, meaning they cannot e
re-used without using that specific workflow.

## Delegator factories

To solve this latter problem &mdash; allowing re-use of middleware without
requiring the specific pipeline &mdash; we provide another approach:
[delegator factories](https://docs.zendframework.com/zend-expressive/features/container/delegator-factories/).

Available since version 2 of the Expressive skeleton, delegator factories
intercept creation of a service, and allow you to act on the service before
returning it, or replace it with another instance entirely!

The above trait could be rewritten as a delegator factory:

```php
namespace Acme\Api;

use Acme\AuthenticationMiddleware;
use Acme\ContentNegotiationMiddleware;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Application;
use Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware;
use Zend\Expressive\Router\RouterInterface;

class CommonApiPipelineDelegatorFactory
{
    public function __invoke(ContainerInterface $container, $name, callable $callback)
    {
        $nested = new Application(
          $container->get(RouterInterface::class),
          $container
        );

        $nested->pipe(AuthenticationMiddleware::class);
        $nested->pipe(ContentValidationMiddleware::class);
        $nested->pipe(BodyParamsMiddleware::class);
        $nested->pipe(BookValidationMiddleware::class);

        // Inject the middleware service requested:
        $nested->pipe($callback());

        return $nested;
    }
}
```

You could then register this with any service that needs the pipeline, without
needing to change their factories. As an example, you could have the following
in either the `config/autoload/dependencies.global.php` file or the
`Acme\ConfigProvider` class, if defined:

```php
'dependencies' => [
    'factories' => [
        \Acme\Api\CreateBookMiddleware::class => \Acme\Api\CreateBookMiddlewareFactory::class,
        \Acme\Api\DeleteBookMiddleware::class => \Acme\Api\DeleteBookMiddlewareFactory::class,
        \Acme\Api\UpdateBookMiddleware::class => \Acme\Api\UpdateBookMiddlewareFactory::class,
    ],
    'delegators' => [
        \Acme\Api\CreateBookMiddleware::class => [
            \Acme\Api\CommonApiPipelineDelegatorFactory::class,
        ],
        \Acme\Api\DeleteBookMiddleware::class => [
            \Acme\Api\CommonApiPipelineDelegatorFactory::class,
        ],
        \Acme\Api\UpdateBookMiddleware::class => [
            \Acme\Api\CommonApiPipelineDelegatorFactory::class,
        ],
    ],
],
```

This approach offers re-usability even when a given middleware may not have
expected to be used in a specific workflow!

## Middleware all the way down!

We hope this tutorial demonstrates the power and flexibility of Expressive, and
how you can create workflows that are granular even to specific middleware. We
covered a number of features in this post:

- Pipeline middleware that operates for all requests.
- Path-segregated middleware.
- Middleware nesting via lists of middleware.
- Returning pipelines or applications from individual service factories.
- Using delegator factories to create and return nested pipelines or
  applications.

## Updates

- **2017-03-15 16:02:00T-0500**: Added note about order of middleware execution.
