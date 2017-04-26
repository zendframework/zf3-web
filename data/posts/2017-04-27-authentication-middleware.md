---
layout: post
title: Middleware authentication
date: 2017-04-27T13:20:00-05:00
author: Enrico Zimuel
url_author: http://www.zimuel.it
permalink: /blog/2017-04-27-authentication-middleware.html
categories:
- blog
- expressive
- zend-authentication

---

Most web applications need to guarantee access only to authorized users and
allow only specific actions based on user roles. Implementing authentication and
authorization in a PHP application it may be not trivial because they could be
many parts to be changed. For instance, if you have an MVC design you may need
to change code in the dispatch, add an authentication layer as first event in
the execution flow and maybe apply some restrictions even in your controllers.

This can be trivial and it really depends on the architecture design and PHP
framework used.

Using a middleware approach, everything become simpler and more natural. In this
article, we will demonstrate how to provide authentication in a PSR-7 middleware
application using [Expressive](https://docs.zendframework.com/zend-expressive/)
and [zend-authentication](https://docs.zendframework.com/zend-authentication).
We will build a simple authentication system using a login page with username
and password credentials.

Since the content of this post is quite long, we'll show the authorization part
in a separate blog post.

## Authentication

`zend-authentication` is a component that offers different adapters or custom
implementation for all the use cases.

This component exposes the `Zend\Authentication\Adapter\AdapterInterface`
interface with a single `authenticate()` function to be implemented:

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

The `authenticate()` function performs the authentication and returns a
`Zend\Authentication\Result` object. This `Result` object contains the
authentication code and the user's identity, in case of success.
The authentication code are defined using the following constants:

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

For instance, if we want to implement a login page with `username` and `password`
authentication we can use a custom adapter like this:

```php
namespace Auth\Service;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;

class MyAuthAdapter implements AdapterInterface
{
    protected $username;
    protected $password;

    public function __construct(/** any dependencies */)
    {
    }

    public function setUsername(string $username)
    {
        $this->username = $username;
    }

    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    /**
     * Performs an authentication attempt
     *
     * @return Result
     */
    public function authenticate()
    {
        // retrieve the user's information (e.g. from a database)
        // and store the result in $row (e.g. associative array)
        // we stored the passwords using PHP password_hash() function

        if (password_verify($this->password, $row['password'])) {
            return new Result(Result::SUCCESS, $row);
        } else {
            return new Result(Result::FAILURE_CREDENTIAL_INVALID, $this->username);
        }
    }
}
```

## Authentication Service

Using this adapter, we can create an *Authentication Service* and consume it in
a middleware, checking for valid users. Using [zend-servicemanager](https://github.com/zendframework/zend-servicemanager)
we can create this service with `Zend\Authentication\AuthenticationService`.

The *Authentication Service* can be implemented as follows:

```php
namespace Auth\Service;

use Interop\Container\ContainerInterface;
use Zend\Authentication\AuthenticationService;

class MyAuthFactory
{
    public function __invoke(ContainerInterface $container)
    {
        // get dependencies from $container
        $adapter = new MyAuthAdapter(/* any dependencies */);

        return new AuthenticationService(null, $adapter);
    }
}
```

This factory class creates an instance of `MyAuthAdapter` and use it to returns
the `AuthenticationService`. We may need to pass some dependencies to the
custom authentication adapter, e.g. a database connection.
The `Zend\Authentication\AuthenticationService` class accepts two parameters in
construction. The first is the storage identity and the second is the
authentication adapter. If storage is `null` it will be used the PHP Session
mechanism to store the user's identity.

In order to consume the `AuthenticationService` we need to store it in the
service manager. This can be done in different ways. A simple solution is to
add the following configuration key in the application:

```php
return [
    'dependencies' => [
        'factories' => [
            'AuthService' => Auth\Service\MyAuthFactory::class
        ]
    ]
];
```

## Authenticate using a login page

Using a login page, we can create a middleware that render the login form
if the HTTP method is GET and checks for `username` and `password` if HTTP
method is POST. If the credentials are valid we can open a welcome page,
otherwise we send back an error message.

```php
namespace Auth\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Authentication\AuthenticationService;

class LoginAction implements ServerMiddlewareInterface
{
    public function __construct(TemplateRendererInterface $template, AuthenticationService $auth)
    {
        $this->template = $template;
        $this->auth     = $auth;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        if ($request->getMethod() === 'POST') {
            return $this->authenticate($request, $delegate);
        }
        return new HtmlResponse($this->template->render('app::login'));
    }

    public function authenticate(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $params = $request->getParsedBody();
        if (empty($params['username'])) {
            return new HtmlResponse($this->template->render('app::login', [
                'error' => 'The username cannot be empty'
            ]));
        }
        if (empty($params['password'])) {
            return new HtmlResponse($this->template->render('app::login', [
                'username' => $params['username'],
                'error'    => 'The password cannot be empty'
            ]));
        }
        $this->auth->getAdapter()->setUsername($params['username']);
        $this->auth->getAdapter()->setPassword($params['password']);

        $result = $this->auth->authenticate();
        if (!$result->isValid()) {
            return new HtmlResponse($this->template->render('app::login', [
                'username' => $params['username'],
                'error'    => 'The credentials provided are not valid'
            ]));
        }
        return new RedirectResponse('/admin');
    }
}
```

This middleware manages two actions: the login form render and the user's
authentication with username and password sent via POST.

We need to create a factory to provide the dependencies for this middleware:

```php
namespace Auth\Action;

use Interop\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class LoginFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $template = $container->get(TemplateRendererInterface::class);
        $auth     = $container->get('AuthService');

        return new LoginAction($template, $auth);
    }
}
```

The `AuthService` instance is retrieved by the container, implemented by
`zend-servicemanager`. We can use the `/login` URL to render the login form and
to perform the authentication via POST. We can add two simple routes like these:

```php
$app->get('/login', Auth\Action\LoginAction::class);
$app->post('/login', Auth\Action\LoginAction::class);
```


## Authentication middleware

Now that we have the `AuthService` service and the login page in place, we can
create the middleware that checks for authentication, providing the redirect to
the `/login` page if the user is not authenticated.

```php
namespace Auth\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Authentication\AuthenticationService;

class AuthAction implements ServerMiddlewareInterface
{
    protected $auth;

    protected $config;

    public function __construct(AuthenticationService $auth, array $config)
    {
        $this->auth   = $auth;
        $this->config = $config;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        if (!$this->auth->hasIdentity()) {
            return new RedirectResponse('/login');
        }
        $identity = $this->auth->getIdentity();
        return $delegate($request->withAttribute(self::class, $identity));
    }
}
```

This middleware works checking for a valid identity, using the `hasIdentity()`
function of `AuthenticationService`. If the authentication service does not
have an identity we redirect the response to a specific URL, stored in a
`redirect` configuration value.

If the user is authenticated, we continue the execution of the next middleware,
storing the identity in a request attribute. This will facilitate the
consumption of the identity information for the other middlewares. For instance,
imagine you need to retrieve user's information in a middleware, you can get
it as follows:

```php
namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Auth\Action\AuthAction;

class FooAction
{
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $user = $request->getAttribute(AuthAction::class);
        // $user will contains the user's identity
    }
}
```

The `AuthAction` middleware needs some dependencies. These can be passed using a
`AuthFactory` class, implemented as follows:

```php
namespace Auth\Action;

use Interop\Container\ContainerInterface;
use Zend\Authentication\AuthenticationService;
use Exception;

class AuthFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        if (!isset($config['auth']['service'])) {
            throw new Exception('The auth service adapter is not configured');
        }
        if (!isset($config['auth']['redirect'])) {
            throw new Exception('The auth URL redirect is not configured');
        }
        $auth = $container->get($config['auth']['service']);

        return new AuthAction($auth, $config['auth']);
    }
}
```

This factory class reads the configuration key `auth` and get the
*Authentication Service* from the service manager. Finally, in order to consume
this middleware we need to add it in the `zend-servicamager`, using the
following application configuration:

```php
return [
    'dependencies' => [
        'factories' => [
            Action\AuthAction::class => Action\AuthFactory::class
        ]
    ]
];
```


## Require authentication for specific routes

Now that we built the authentication middleware, we can use it to protect
specific routes that require authentication. For instance, image we have a
set of pages that needs authentication, we can protect it adding the
`Auth\Action\AuthAction::class` in the routes, as in the following example:

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
The authentication is provided first as first element. In this way if the user
is not authenticated the `DashBoardAction` and the `ConfigAction` will never be
executed.


## Conclusion

We started this article introducing the needs for authentication and
authorization in web applications and how to implement it using middleware in
PHP. We presented only the authentication part and we will show the
authorization in a future blog post.

We will demonstrate how to authorize actions for specific users using
[zend-permissions-rbac](https://github.com/zendframework/zend-permissions-rbac/)
component.

> ## Save the date!
>
> Want to learn more about Expressive and Zend Framework? What better location
> than ZendCon 2017! ZendCon will be hosted 23-26 October 2017 in Las Vegas,
> Nevada, USA. [Visit the ZendCon website for more
> information](http://www.zendcon.com).
