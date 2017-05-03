---
layout: post
title: Authorize users using Middleware
date: 2017-05-04T11:10:00-05:00
author: Enrico Zimuel
url_author: http://www.zimuel.it
permalink: /blog/2017-05-04-authorization-middleware.html
categories:
- blog
- expressive
- zend-permissions-rbac

---

In a [previous post](/blog/2017-04-26-authentication-middleware.html), we
demonstrated how to authenticate a middleware application in PHP. In this
post we will continue the discussion, showing how to manage authorizations.

We will start from an authenticated user and we will show how to allow or
disable actions for specific users. We will collect users by groups and we
will use a *Role-Based Access Control* (RBAC) system to manage the
authorizations.

We will use the [zendframework/zend-permissions-rbac](https://github.com/zendframework/zend-permissions-rbac)
to implement RBAC.

> If you are not familiar with RBAC and the usage of zend-permissions-rbac
> you can read [this blog post](/blog/2017-04-27-zend-permissions-rbac.html).

## Getting started

This article assumes you have already created the `Auth` module, as described in
our [previous post](/blog/2017-04-26-authentication-middleware.html). For the
purposes of our application, we'll create a new module, `Permission`, in which
we'll put our classes, middleware, and general configuration.

First, if you have not already, install the tooling support:

```bash
$ composer require --dev zendframework/zend-expressive-tooling
```

Next, we'll create the `Permission` module:

```bash
$ ./vendor/bin/expressive module:create Permission
```

With that out of the way, we can get started.

## Authentication

As already mentioned, we will reuse the `Auth` module created in our previous
post. We will reuse the `Auth\Action\AuthAction::class` to get the authenticated
user's data.

## Authorization

In order to manage the authorization we use a RBAC system using the user's role.
A user's role is a string that represents the permission level, e.g.
`administrator` is usually the high level that can access everything.

In our scenario, we want to allow or disable access of specific route to a role
or set of roles. Each route represents a *permission* in RBAC terminology.

We can use [zendframework/zend-permissions-rbac](https://github.com/zendframework/zend-permissions-rbac)
to manage the RBAC system using a PHP configuration file storing the list of
roles and permissions. Using `zendframework/zend-permissions-rbac` we can also
manage permissions inheritance.

For instance, imagine to implement a blog application, we can have the following
roles:

- `administrator`;
- `editor`;
- `contributor`.

A `contributor` can create/edit/delete only the posts created by himself. The
`editor` can create/edit/delete all the posts and publish a post, that means
enabling the view of post in the public web site. The `administrator` can
perform all the actions, e.g. including changing the blog's configuration.

This is a perfect use case for using permission inheritance. In fact, the
`administrator` role inheritances permission of `editor`, and the `editor` role
inheritances the permission of `contributor`.

To manage the previous scenario, we can use the following configuration file:

```php
// In src/Permission/src/config/rbac.php:

return [
    'roles' => [
        'administrator' => [],
        'editor'        => ['admin'],
        'contributor'   => ['editor']
    ],
    'permissions' => [
        'contributor' => [
            'admin.dashboard',
            'admin.posts'
        ],
        'editor' => [
            'admin.publish'
        ],
        'administrator' => [
            'admin.settings'
        ]
    ]
];
```

In this file we have specified 3 roles, including the parents relationship using
an array of role names. The parent of `administator` is an empty array, that
means no parents.

The permissions are configured using the `permissions` key. Each role has the
list of permissions, specified with an array of route names.

Al the roles can access the route `admin.dashboard` and `admin.posts`.
The `editor` role can also access the `admin.publish`. The `administrator` can
access all the roles of `contributor` and `editor`. Moreover, only the
`administrator` can access the `admin.settings` route.

> We used the route names as RBAC permissions because in this way we can allow
> URL and HTTP methods using a single resource name. Moreover, in Expressive we
> have a `config/routes.php` file containing all the routes and we can easily
> use it to add authorization, as we did for the authentication.

## Authorization middleware

Now that we have the RBAC configuration in place, we can create a middleware
that performs the check for user authorization.

We can create a `AuthorizationAction` middleware in our `Permission` module as
follows:

```php
namespace Permission\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Permissions\Rbac\Rbac;
use Zend\Permissions\Rbac\AssertionInterface;
use Zend\Permissions\Rbac\RoleInterface;
use Zend\Expressive\Router\RouteResult;
use Permission\Service\PostService;
use Permission\Entity\Post as PostEntity;
use Auth\Action\AuthAction;

class AuthorizationAction implements ServerMiddlewareInterface
{
    protected $rbac;
    protected $postService;

    public function __construct(Rbac $rbac, PostService $postService)
    {
        $this->rbac        = $rbac;
        $this->postService = $postService;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $user = $request->getAttribute(AuthAction::class, false);
        if (false === $user) {
            return new EmptyResponse(401);
        }

        // if a post attribute is present and user is contributor
        $postUrl = $request->getAttribute('post', false);
        if (false !== $postUrl && $user['role'] === 'contributor') {
            $post = $this->postService->getPost($postUrl);
            $assert = new class ($user['username'], $post) implements AssertionInterface {
                public function __construct(string $username, PostEntity $post)
                {
                    $this->username = $username;
                    $this->post     = $post;
                }
                public function assert(Rbac $rbac)
                {
                    return $username === $this->post->getAuthor();
                }
            };
        }

        $route     = $request->getAttribute(RouteResult::class);
        $routeName = $route->getMatchedRoute()->getName();
        if (! $this->rbac->isGranted($user['role'], $routeName, $assert ?? null)) {
            return new EmptyResponse(403);
        }

        return $delegate->process($request);
    }
}
```
If the user is not present the `AuthAction::class` attribute will be false.
In this case we are returning a `401` error, stopping the execution.

If a user is returned from `AuthAction::class` attribute this means that we
have an authenticated user.

> The authentication is performed by the `Auth\Action\AuthAction` class that
> stores the `AuthAction::class` attribute in the request.
> See the [previous post]((/blog/2017-04-26-authentication-middleware.html)) for
> more information.

This middleware performs the authorization check using `isGranted($role, $permission)`
where `$role` is the user's role (`$user['role']`) and `$permission` is the route
name, retrieved by the `RouteResult::class` attribute. If the role is granted,
we continue the execution flow with the delegate middleware. Otherwise, we stop
the execution with a `403` error.

We manage also the case when the user is a `contributor` and there is a `post`
attribute in the request (e.g. /admin/posts/{post}). That means, someone is
performing some actions on a specific post. To perform this action, we require
that the owner of the post should be the same of the authenticated user.

This will prevent a `contributor` to change the content of a post if he/she is
not the author. We managed this special case using a [dynamic assertion](https://zendframework.github.io/zend-permissions-rbac/intro/#dynamic-assertions).
We used the anonymous class feature of PHP 7 to create a custom assertion
(`$assert`) to check if the authenticated `username` is the same of the author's
post. We used a general `PostEntity` class with a `getAuthor()` function.

In order to retrieve for the route name, we used the `RouteResult::class`
attribute provided by [Expressive](https://docs.zendframework.com/zend-expressive/).
This attribute facilitates the access to the matched route.

The `AuthorizationAction` middleware require the `Rbac` and the `PostService`
dependencies. The first is an instance of `Zend\Permissions\Rbac\Rbac` and the
second is a general service to manage the Post, e.g. a class that performs some
SQL to retrieve the post data from a database.

To inject these dependencies we used `AuthorizationFactory`, like the follows:

```php
namespace Permission\Action;

use Interop\Container\ContainerInterface;
use Zend\Permissions\Rbac\Rbac;
use Zend\Permissions\Rbac\Role;
use Permission\Service\PostService;
use Exception;

class AuthorizationFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        if (!isset($config['rbac']['roles'])) {
            throw new Exception('Rbac roles are not configured');
        }
        if (!isset($config['rbac']['permissions'])) {
            throw new Exception('Rbac permissions are not configured');
        }

        $rbac = new Rbac();
        $rbac->setCreateMissingRoles(true);

        // roles and parents
        foreach ($config['rbac']['roles'] as $role => $parents) {
            $rbac->addRole($role, $parents);
        }
        // permissions
        foreach ($config['rbac']['permissions'] as $role => $permissions) {
            foreach ($permissions as $perm) {
                $rbac->getRole($role)->addPermission($perm);
            }
        }
        $post = $container->get(PostService::class);

        return new AuthorizationAction($rbac, $post);
    }
}
```

This factory class built the `Rbac` object using the configuration file
stored in `src/Permission/src/config/rbac.php`. We read all the roles and the
permissions following the order in the array. It is important to enable the
creation of missing roles in the Rbac object using the function
`setCreateMissingRoles(true)`. This is required to be sure to create all the
roles even if we add it without an order. For instance, without this setting,
the following configuration will throw an exception:

```php
return [
    'roles' => [
        'contributor'   => ['editor'],
        'editor'        => ['administrator'],
        'administrator' => []
    ]
]
```

because the `editor` and the `administrator` roles are specified as parent
before they were created.

Finally, we can configure the `Permission` module adding the following
dependencies:

```php
// In src/Permission/src/ConfigProvider.php:

// Update the following methods:
public function __invoke()
{
    return [
        'dependencies' => $this->getDependencies(),
        'rbac'         => require 'config/rbac.php'
    ];
}
public function getDependencies()
{
    return [
        'factories' => [
            Service\PostService::class => Service\PostFactory::class,
            Action\AuthorizationAction::class => Action\AuthorizationFactory::class,
        ],
    ];
}
```

## Configure the route for authorization

To enable authorization on a specific route we need to add the
`Permission\Action\AuthorizationAction` middleware in the route, as follows:

```php
$app->get('/admin/dashboard', [
    Auth\Action\AuthAction::class,
    Permission\Action\AuthorizationAction::class,
    Admin\Action\DashboardAction::class
], 'admin.dashboard');
```

This is an example of `GET /admin/dashboard` route with `admin.dashboard` as
name. We added Authentication and Authorization before the execution of the
DashboardAction using the middlewares `Auth\Action\AuthAction::class` and
`Permission\Action\AuthorizationAction::class`. The order of the middleware
array is important, first we need to authenticate a user and after we can check
for authorization.

We need to add the `AuthorizationAction` middleware in all the routes that we
need to authorize.

## Conclusion

This article, together with the one related to the [authentication middleware](/blog/2017-04-26-authentication-middleware.html)
demonstrate how to accomodate authentication and authorization within
middleware in PHP.

We showed how to create two separate Expressive module `Auth` and `Permission`
to provide authentication and authorization using `zend-auth` and `zend-permissions-rbac`.

We showed also the usage of dynamic assertion for specific permission based on
the role and username of an authentiation user.

The blog use case proposed in this article is quite simple but the architecture
used can be applied also in complex scenario, to manage permissions based on
different requirements.

In the future we will talk again about authentication and authorization, since
this is a very important aspect of web applications.


> ## Save the date!
>
> Want to learn more about Expressive and Zend Framework? What better location
> than ZendCon 2017! ZendCon will be hosted 23-26 October 2017 in Las Vegas,
> Nevada, USA. [Visit the ZendCon website for more
> information](http://www.zendcon.com).
