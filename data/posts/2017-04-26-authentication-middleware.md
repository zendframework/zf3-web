---
layout: post
title: Middleware authentication
date: 2017-04-26T11:10:00-05:00
updated: 2017-06-19T08:50-05:00
author: Enrico Zimuel
url_author: http://www.zimuel.it
permalink: /blog/2017-04-26-authentication-middleware.html
categories:
- blog
- expressive
- zend-authentication

---

Many web applications require restricting specific areas to _authenticated_
users, and may further restrict specific actions to _authorized_ user roles.
Implementing authentication and authorization in a PHP application is often
non-trivial as doing so requires altering the application workflow. For
instance, if you have an MVC design, you may need to change the dispatch logic
to add an authentication layer as an initial event in the execution flow, and
perhaps apply restrictions within your controllers.

Using a middleware approach is simpler and more natural, as middleware easily
accommodates workflow changes. In this article, we will demonstrate how to
provide authentication in a PSR-7 middleware application using
[Expressive](https://docs.zendframework.com/zend-expressive/) and
[zend-authentication](https://docs.zendframework.com/zend-authentication).  We
will build a simple authentication system using a login page with username and
password credentials.

> Since the content of this post is quite long, [we detail authorization 
> in a separate blog post](/blog/2017-05-04-authorization-middleware.html).

## Getting started

This article assumes you have already created an Expressive application. For the
purposes of our application, we'll create a new module, `Auth`, in which we'll
put our classes, middleware, and general configuration.

First, if you have not already, install the tooling support:

```bash
$ composer require --dev zendframework/zend-expressive-tooling
```

Next, we'll create the `Auth` module:

```bash
$ ./vendor/bin/expressive module:create Auth
```

With that out of the way, we can get started.

## Authentication

The zend-authentication component offers an adapter-based authentication
solution, with both a number of concrete adapters as well as mechanisms for
creating and consuming custom adapters.

The component exposes `Zend\Authentication\Adapter\AdapterInterface`, which
defines a single `authenticate()` method:

```php
namespace Zend\Authentication\Adapter;

interface AdapterInterface
{
    /**
     * Performs an authentication attempt
     *
     * @return \Zend\Authentication\Result
     * @throws Exception\ExceptionInterface if authentication cannot be performed
     */
    public function authenticate();
}
```

Adapters implementing the `authenticate()` method perform the logic necessary to
authenticate a request, and return the results via a
`Zend\Authentication\Result` object. This `Result` object contains the
authentication result code and, in the case of success, the user's identity.
The authentication result codes are defined using the following constants:

```php
namespace Zend\Authentication;

class Result
{
    const SUCCESS = 1;
    const FAILURE = 0;
    const FAILURE_IDENTITY_NOT_FOUND = -1;
    const FAILURE_IDENTITY_AMBIGUOUS = -2;
    const FAILURE_CREDENTIAL_INVALID = -3;
    const FAILURE_UNCATEGORIZED = -4;
}
```

If we want to implement a login page with `username` and `password`
authentication, we can create a custom adapter such as the following:

```php
// In src/Auth/src/MyAuthAdapter.php:

namespace Auth;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;

class MyAuthAdapter implements AdapterInterface
{
    private $password;
    private $username;

    public function __construct(/* any dependencies */)
    {
        // Likely assign dependencies to properties
    }

    public function setPassword(string $password) : void
    {
        $this->password = $password;
    }

    public function setUsername(string $username) : void
    {
        $this->username = $username;
    }

    /**
     * Performs an authentication attempt
     *
     * @return Result
     */
    public function authenticate()
    {
        // Retrieve the user's information (e.g. from a database)
        // and store the result in $row (e.g. associative array).
        // If you do something like this, always store the passwords using the
        // PHP password_hash() function!

        if (password_verify($this->password, $row['password'])) {
            return new Result(Result::SUCCESS, $row);
        }

        return new Result(Result::FAILURE_CREDENTIAL_INVALID, $this->username);
    }
}
```

We will want a factory for this service as well, so that we can seed the
username and password to it later:

```php
// In src/Auth/src/MyAuthAdapterFactory.php:

namespace Auth;

use Interop\Container\ContainerInterface;
use Zend\Authentication\AuthenticationService;

class MyAuthAdapterFactory
{
    public function __invoke(ContainerInterface $container)
    {
        // Retrieve any dependencies from the container when creating the instance
        return new MyAuthAdapter(/* any dependencies */);
    }
}
```

This factory class creates and returns an instance of `MyAuthAdapter`.
We may need to pass some dependencies to its constructor, such as a database
connection; these would be fetched from the container.

## Authentication Service

We can now create a `Zend\Authentication\AuthenticationService`
that composes our adapter, and then consume the `AuthenticationService` in
middleware to check for a valid user. Let's now create a factory for the
`AuthenticationService`:

```php
// in src/Auth/src/AuthenticationServiceFactory.php:

namespace Auth;

use Interop\Container\ContainerInterface;
use Zend\Authentication\AuthenticationService;

class AuthenticationServiceFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new AuthenticationService(
            null,
            $container->get(MyAuthAdapter::class)
        );
    }
}
```

This factory class retrieves an instance of the `MyAuthAdapter` service and use
it to return an `AuthenticationService` instance.  The `AuthenticationService`
class accepts two parameters:

- A storage service instance, for persisting the user identity. If none is
  provided, the built-in PHP session mechanisms will be used.
- The actual adapter to use for authentication.

Now that we have created both the custom adapter, as well as factories for the
adapter and the `AuthenticationService`, we need to configure our application
dependencies to use them:

```php
// In src/Auth/src/ConfigProvider.php:

// Add the following import statement at the top of the classfile:
use Zend\Authentication\AuthenticationService;

// And update the following method:
public function getDependencies()
{
    return [
        'factories' => [
            AuthenticationService::class => AuthenticationServiceFactory::class,
            MyAuthAdapter::class => MyAuthAdapterFactory::class,
        ],
    ];
}
```

## Authenticate using a login page

With an authentication mechanism in place, we now need to create middleware to
render the login form. This middleware will do the following:

- for `GET` requests, it will render the login form.
- for `POST` requests, it will check for credentials and then attempt to
  validate them.
  - for valid authentication requests, we will redirect to a welcome page
  - for invalid requests, we will provide an error message and redisplay the
    form.

Let's create the middleware now:

```php
// In src/Auth/src/Action/LoginAction.php:

namespace Auth\Action;

use Auth\MyAuthAdapter;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Template\TemplateRendererInterface;

class LoginAction implements ServerMiddlewareInterface
{
    private $auth;
    private $authAdapter;
    private $template;

    public function __construct(
        TemplateRendererInterface $template,
        AuthenticationService $auth,
        MyAuthAdapter $authAdapter
    ) {
        $this->template    = $template;
        $this->auth        = $auth;
        $this->authAdapter = $authAdapter;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        if ($request->getMethod() === 'POST') {
            return $this->authenticate($request);
        }

        return new HtmlResponse($this->template->render('auth::login'));
    }

    public function authenticate(ServerRequestInterface $request)
    {
        $params = $request->getParsedBody();

        if (empty($params['username'])) {
            return new HtmlResponse($this->template->render('auth::login', [
                'error' => 'The username cannot be empty',
            ]));
        }

        if (empty($params['password'])) {
            return new HtmlResponse($this->template->render('auth::login', [
                'username' => $params['username'],
                'error'    => 'The password cannot be empty',
            ]));
        }

        $this->authAdapter->setUsername($params['username']);
        $this->authAdapter->setPassword($params['password']);

        $result = $this->auth->authenticate();
        if (!$result->isValid()) {
            return new HtmlResponse($this->template->render('auth::login', [
                'username' => $params['username'],
                'error'    => 'The credentials provided are not valid',
            ]));
        }

        return new RedirectResponse('/admin');
    }
}
```

This middleware manages two actions: rendering the login form, and
authenticating the user's credentials when submitted via a `POST` request.

> You will also need to ensure that you have:
>
> - Created a `login` template.
> - Added configuration to map the `auth` template namespace to one or more
>   filesystem paths.
>
> We leave those tasks as an exercise to the reader.

We now need to create a factory to provide the dependencies for this
middleware:

```php
// In src/Auth/src/Action/LoginActionFactory.php:

namespace Auth\Action;

use Auth\MyAuthAdapter;
use Interop\Container\ContainerInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Expressive\Template\TemplateRendererInterface;

class LoginActionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new LoginAction(
            $container->get(TemplateRendererInterface::class),
            $container->get(AuthenticationService::class),
            $container->get(MyAuthAdapter::class)
        );
    }
}
```

Map the middleware to this factory in your dependencies configuration witin the
`ConfigProvider`:

```php
// In src/Auth/src/ConfigProvider.php,

// Update the following method to read as follows:
public function getDependencies()
{
    return [
        'factories' => [
            Action\LoginAction::class => Action\LoginActionFactory::class,
            AuthenticationService::class => AuthenticationServiceFactory::class,
            MyAuthAdapter::class => MyAuthAdapterFactory::class,
        ],
    ];
}
```

> ### Use zend-servicemanager's ReflectionBasedAbstractFactory
>
> If you are using zend-servicemanager in your application, you could skip the
> step of creating the factory, and instead map the middleware to
> `Zend\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory`.

Finally, we can create appropriate routes. We'll map `/login` to the
`LoginAction` now, and allow it to react to either the `GET` or `POST` methods:

```php
// in config/routes.php:
$app->route('/login', Auth\Action\LoginAction::class, ['GET', 'POST'], 'login');
```

Alternately, the above could be written as two separate statements:

```php
// in config/routes.php:
$app->get('/login', Auth\Action\LoginAction::class, 'login');
$app->post('/login', Auth\Action\LoginAction::class);
```

## Authentication middleware

Now that we have the authentication service and its adapter and the login
middleware in place, we can create middleware that checks for authenticated
users, having it redirect to the `/login` page if the user is not authenticated.

```php
// In src/Auth/src/Action/AuthAction.php:

namespace Auth\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Diactoros\Response\RedirectResponse;

class AuthAction implements ServerMiddlewareInterface
{
    private $auth;

    public function __construct(AuthenticationService $auth)
    {
        $this->auth = $auth;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        if (! $this->auth->hasIdentity()) {
            return new RedirectResponse('/login');
        }

        $identity = $this->auth->getIdentity();
        return $delegate->process($request->withAttribute(self::class, $identity));
    }
}
```

This middleware checks for a valid identity using the `hasIdentity()` method of
`AuthenticationService`. If no identity is present, we redirect the `redirect`
configuration value.

If the user is authenticated, we continue the execution of the next middleware,
storing the identity in a request attribute. This facilitates consumption of the
identity information in subsequent middleware layers. For instance, imagine you
need to retrieve the user's information:

```php
namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;

class FooAction
{
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $user = $request->getAttribute(AuthAction::class);
        // $user will contains the user's identity
    }
}
```

The `AuthAction` middleware needs some dependencies, so we will need to create
and register a factory for it as well.

First, the factory:

```php
// In src/Auth/src/Action/AuthActionFactory.php:

namespace Auth\Action;

use Interop\Container\ContainerInterface;
use Zend\Authentication\AuthenticationService;
use Exception;

class AuthActionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new AuthAction($container->get(AuthenticationService::class));
    }
}
```

And then mapping it:

```php
// In src/Auth/src/ConfigProvider.php:


// Update the following method to read as follows:
public function getDependencies()
{
    return [
        'factories' => [
            Action\AuthAction::class => Action\AuthActionFactory::class,
            Action\LoginAction::class => Action\LoginActionFactory::class,
            AuthenticationService::class => AuthenticationServiceFactory::class,
            MyAuthAdapter::class => MyAuthAdapterFactory::class,
        ],
    ];
}
```

> Like the `LoginActionFactory` above, you could skip the factory creation and
> instead use the `ReflectionBasedAbstractFactory` if using zend-servicemanager.

## Require authentication for specific routes

Now that we built the authentication middleware, we can use it to protect
specific routes that require authentication. For instance, for each route that
needs authentication, we can modify the routing to create a pipeline that
incorporates our `AuthAction` middleware early:

```php
$app->get('/admin', [
    Auth\Action\AuthAction::class,
    App\Action\DashBoardAction::class
], 'admin');

$app->get('/admin/config', [
    Auth\Action\AuthAction::class,
    App\Action\ConfigAction::class
], 'admin.config');
```

The order of execution for the middleware is the order of the array elements.
Since the `AuthAction` middleware is provided as the first element, if a user is
not authenticated when requesting either the admin dashboard or config page,
they will be immediately redirected to the login page instead.

## Conclusion

There are many ways to accommodate authentication within middleware
applications; this is just one. Our goal was to demonstrate the ease with which
you may compose authentication into existing workflows by creating middleware
that intercepts the request early within a pipeline.

You could certainly make a number of improvements to the workflow:

- The path to the login page could be configurable.
- You could capture the original request path in order to allow redirecting to
  it following successful login.
- You could introduce rate limiting of login requests.

These are each interesting exercises for you to try!

As noted in the introduction, this article demonstrates only _authentication_.
Our next article [details how to use zend-permissions-rbac](/blog/2017-04-27-zend-permissions-rbac.html),
and a later article details [authorization middleware using an RBAC](/blog/2017-05-04-authorization-middleware.html).

## Updates

- _2017-06-19_: Updated comment at start of article to link to post on
  authorization middleware, and last paragraph to link to same post, as well as
  the post on creating RBACs.

> ## Save the date!
>
> Want to learn more about Expressive and Zend Framework? What better location
> than ZendCon 2017! ZendCon will be hosted 23-26 October 2017 in Las Vegas,
> Nevada, USA. [Visit the ZendCon website for more
> information](http://www.zendcon.com).
