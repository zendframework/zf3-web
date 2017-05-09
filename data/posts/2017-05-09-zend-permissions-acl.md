---
layout: post
title: Manage permissions with zend-permissions-acl
date: 2017-05-09T17:00:00-05:00
author: Matthew Weier O'Phinney
url_author: https://mwop.net/
permalink: /blog/2017-05-09-zend-permissions-acl.html
categories:
- blog
- components
- zend-permissions-acl

---

The last couple posts have been around authorization, the act of determining if
a given identity has access to a resource. We covered usage of [role based
access controls](/blog/2017-04-27-zend-permissions-rbac.html), as well as
[middleware that uses an RBAC](/blog/2017-05-04-authorization-middleware.html).

In this post, we'll explore another option provided by Zend Framework,
[zend-permissions-acl](https://docs.zendframework.com/zend-permissions-acl/),
which implements Access Control Lists (ACL).

This post will follow the same basic format as the one covering
zend-permissions-rbac, using the same basic examples.

# Installing zend-permissions-acl

Just as you would any of our components, install zend-permissions-acl via
Composer:

```bash
$ composer require zendframework/zend-permissions-acl
```

The component has no requirements at this time other than a PHP version of at
least 5.5.

## Vocabulary

In ACL systems, we have three concepts:

- a **resource** is something to which we control access.
- a **role** is something that will request access to a _resource_.
- Each _resource_ has **privileges** for which access will be requested to
  specific _roles_.

As an example, an _author_ might request to _create_ (privilege) a _blog post_
(resource); later, an _editor_ (role) might request to _edit_ (privilege) a _blog
post_ (resource).

The chief difference to RBAC is that RBAC essentially combines the resource and
privilege into a single item. By separating them, you can create a set of
discrete permissions for your _entire_ application, and then create roles with
multiple-inheritance in order to implement fine-grained permissions.

## ACLs

An ACL is created by instantiating the `Acl` class:

```php
use Zend\Permissions\Acl\Acl;

$acl = new Acl();
```

Once that instance is available, we can start adding roles, resources, and
privileges.

For this blog post, our ACL will be used for a content-based website.

## Roles

Roles are added via the `$acl->addRole()` method. This method takes either a
string role name, or a `Zend\Permissions\Acl\Role\RoleInterface` instance.

Let's start with a "guest" **role**, that only allows "read" permissions.

```php
use Zend\Permissions\Acl\Role\GenericRole as Role;

// Create some roles
$guest = new Role('guest');
$acl->addRole($guest);

// OR
$acl->addRole('guest');
```

> ### Referencing roles and resources
>
> Roles are simply strings. We model them as objects in zend-permissions-acl in
> order to provide strong typing, but the only requirement is that they return
> a string role name. As such, when creating permissions, you can use either a
> role instance, or the equivalent name.
>
> The same is true for resources, which we cover in a later section.

By default, zend-permissions-acl implements a _whitelist_ approach. A
_whitelist_ **denies access** to everything _unless_ it is explicitly
_whitelisted_. (This is as opposed to a _blacklist_, where access is allowed to
everything unless it is in the _blacklist_.) Unless you really know what you're
doing we do not suggest toggling this; whitelists are widely regarded as a best
practice for security.

What that means is that, out of the gate, while we _can_ do some privilege
assertions:

```php
$acl->isAllowed('guest', 'blog', 'read');
$acl->isAllowed('guest', 'blog', 'write');
```

these will always return `false`, denying access. So, we need to start adding
privileges.

## Privileges

**Privileges** are assigned using `$acl->allow()`.

For the `guest` role, we'll allow the `read` privilege on _any_ resource:

```php
$acl->allow('guest', null, 'read');
```

The second argument to `allow()` is the resource (or resources); specifying
`null` indicates the privilege applies to _all_ resources. If we re-run the
above assertions, we get the following:

```php
$acl->isAllowed('guest', 'blog', 'read');  // true
$acl->isAllowed('guest', 'blog', 'write'); // false
```

> ### Unknown roles or resources
>
> One thing to note: if either the role or resource used with `isAllowed()` does
> not exist, this method _raises an exception_, specifically a
> `Zend\Permissions\Acl\Exception\InvalidArgumentException`, indicating the role
> or resource could not be found.
> 
> In many situations, this may not be what you want; you may want to handle
> non-existent roles and/or resources gracefully. You could do this in a couple
> ways. First, you can test to see if the role or resource exists _before_ you
> check the permissions, using `hasRole()` and/or `hasResource()`:
> 
> ```php
> if (! $acl->hasRole($foo)) {
>     // failed, due to missing role
> }
> if (! $acl->hasResource($bar)) {
>     // failed, due to missing resource
> }
> if (! $acl->isAllowed($foo, $bar, $privilege)) {
>     // failed, due to invalid privilege
> }
> ```
> 
> Alternately, wrap the `isAllowed()` call in a try/catch block:
> 
> ```php
> try {
>     if (! $acl->isAllowed($foo, $bar, $privilege)) {
>         // failed, due to missing privileges
>     }
> } catch (AclInvalidArgumentException $e) {
>       // failed, due to missing role or resource
> }
> ```
> 
> Personally, I don't like to use exceptions for application flow, so I recommend
> the first solution. That said, in most cases, you will be working with a role
> instance that you've just added to the ACL, and should only perform assertions
> against known resources.

## Resources

Now let's add some actual resources. These are almost exactly like roles in
terms of usage: you create a `ResourceInterface` instance to pass to the ACL,
or, more simply, a string; resources are added via the `$acl->addResource()`
method.

```php
use Zend\Permissions\Acl\Resource\GenericResource as Resource;

$resource = new Resource('blog');
$acl->addResource($resource);

// OR:
$acl->addResource('blog');
```

A resource is anything to which you want to apply permissions. In the remaining
  examples of this post, we'll use a "blog" as the resource, and provide a
  variety of permissions related to it.

## Inheritance

Let's say we want to build on our previous examples, and create an "editor" role
that also incorporates the permissions of the "guest" role, and adds a "write"
permission to the "blog" resource.

Unlike RBAC, roles themselves contain no information about inheritance; instead,
the ACL takes care of that when you add the role to the ACL:

```php
$editor = new Role('editor');
$acl->addRole($editor, $guest); // OR:
$acl->addRole($editor, 'guest');
```

The above creates a new role, `editor`, which inherits the permissions of our
`guest` role. Now, let's add a privilege allowing editors to `write` to our
`blog`:

```php
$acl->allow('editor', 'blog', 'write');
```

With this in place, let's do some assertions:

```php
$acl->isAllowed('editor', 'blog', 'write'); // true
$acl->isAllowed('editor', 'blog', 'read');  // true
$acl->isAllowed('guest',  'blog', 'write'); // false
```

Another role might be a "reviewer" who can "moderate" content:

```php
$acl->addRole('reviewer', 'guest');
$acl->allow('reviewer', 'blog', 'moderate');

$acl->isAllowed('reviewer', 'blog', 'moderate'); // true
$acl->isAllowed('reviewer', 'blog', 'write');    // false; editor only!
$acl->isAllowed('reviewer', 'blog', 'read');     // true
$acl->isAllowed('guest',    'blog', 'moderate'); // false
```

Let's create another, an "admin" who can do **all** of the above, but also has
permissions for "settings":

```php
$acl->addRole('admin', ['guest', 'editor', 'reviewer']);
$acl->allow('admin', 'blog', 'settings');

$acl->isAllowed('admin',    'blog', 'settings'); // true
$acl->isAllowed('admin',    'blog', 'write');    // true
$acl->isAllowed('admin',    'blog', 'moderate'); // true
$acl->isAllowed('admin',    'blog', 'read');     // true
$acl->isAllowed('editor',   'blog', 'settings'); // false
$acl->isAllowed('reviewer', 'blog', 'settings'); // false
$acl->isAllowed('guest',    'blog', 'write');    // false
```

Note that the `addRole()` call here provides an _array_ of roles as the second
value this time; when called this way, the new role will inherit the privileges
of _every_ role listed; this allows for multiple-inheritance at the role level.

> ### Resource inheritance
>
> Resource inheritance works exactly the same as Role inheritance! Add one or
> more parent resources when calling `addResource()` on the ACL, and any
> privileges assigned to that parent resource will also apply to the new
> resource.
>
> As an example, I could have a "news" section in my website that has the
> same privilege and role schema as my blog:
>
> ```php
> $acl->addResource('news', 'blog');
> ```

## Fun with privileges!

Privileges are assigned using `allow()`. Interestingly, like `addRole()` and
`addResource()`, the role and resource arguments presented may be _arrays_ of
each; in fact, so can the privileges themselves!

As an example, we could do the following:

```php
$acl->allow(
    ['reviewer', 'editor'],
    ['blog', 'homepage'],
    ['write', 'maintenance']
);
```

This would assign the "write" and "maintenance" privileges on each of the "blog"
and "homepage" resources to the "reviewer" and "editor" roles! Due to
inheritance, the "admin" role would also gain these privileges.

## Creating your ACL

When should you create your ACL, exactly? And should it contain all roles and
permissions?

Typically, you will create a finite number of application or domain permissions.
In our above examples, we could omit the `blog` resource and apply the ACL only
within the `blog` domain (for example, only within a module of a zend-mvc or
Expressive application); alternately, it could be an application-wide ACL, with
resources segregated by specific domain within the application.

In either case, you will generally:

- Create a finite set of well-known roles, resources, and privileges as a global
  or per-domain ACL.
- Create a custom role for the current user, typically inheriting from the set
  of well-known roles.
- Validate the current user against the ACL.

Unlike RBAC, you typically will not add custom permissions for a user. The
reason for this is due to the complexity of storing the combination of roles,
resources, and privileges in a database. Storing roles is trivial:

| user_id | fullname | roles           |
| :------ | :------- | :-------------- |
| mario   | Mario    | editor,reviewer |

You could then create the role by splitting the `roles` field and assigning each
as parents:

```php
$acl->addRole($user->getId(), explode(',', $user->getRoles());
```

However, for fine-grained permissions, you would essentially need an additional
lookup table mapping the user to a resource and list of privileges:

| user_id | resource | privileges    |
| ------- | -------- | ------------- |
| mario   | blog     | update,delete |
| mario   | news     | update        |

While it can be done, it's resource and code intensive.

Putting it all together, let's say the user "mario" has logged in, with the role
"editor"; further, let's assume that the identity instance for our user
implements `RoleInterface`. If our ACL is already populated per the above
examples, I might do the following:

```php
$acl->addRole($mario, $mario->getRoles());

$acl->isAllowed($mario, 'blog', 'settings'); // false; admin only!
$acl->isAllowed($mario, 'blog', 'write');    // true; all editors
$acl->isAllowed($mario, 'blog', 'read');     // true; all guests
```

Now, let's say we've gone to the work of creating the join table necessary for
storing user ACL information; we might have something like the following to
further populate the ACL:

```php
foreach ($mario->getPrivileges() as $resource => $privileges) {
    $acl->allow($mario, $resource, explode(',', $privileges));
}
```

We could then do the following assertions:

```php
$acl->isAllowed($mario,   'blog', 'update'); // true
$acl->isAllowed('editor', 'blog', 'update'); // false; mario only!
$acl->isAllowed($mario,   'blog', 'delete'); // true
$acl->isAllowed('editor', 'blog', 'delete'); // false; mario only!
```

## Custom assertions

Fine-grained as the privilege system can be, sometimes it's not enough.

As an example, we may want to implement a rule that the _creator_ of a content
item in our website always has rights to _edit_ the item. How would we implement
that with the above system?

zend-permissions-acl allows you to do so via dynamic assertions. Such assertions
are classes that implement `Zend\Permissions\Acl\Assertion\AssertionInterface`,
which defines a single method:

```php
namespace Zend\Permissions\Assertion;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;

interface AssertionInterface
{
    /**
     * @return bool
     */
    public function assert(
        Acl $acl,
        RoleInterface $role = null,
        ResourceInterface $resource = null,
        $privilege = null
    );
}
```

For the sake of this example, let's assume:

- We cast our identity to a `RoleInterface` instance after retrieval.
- The content item is represented as an object.
- The object has a method `getCreatorUsername()` that will return the same
  username as we might have in our custom identity from the previous example.
- If the username is the same as the custom identity, allow any privileges.

Because we have PHP 7 at our disposal, we'll create the assertion as an
anonymous class:

```php
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;

$assertion = new class ($identity, $content) implements AssertionInterface {
    private $content;
    private $identity;

    public function __construct(RoleInterface $identity, $content)
    {
        $this->identity = $identity;
        $this->content = $content;
    }

    /**
     * @return bool
     */
    public function assert(
        Acl $acl,
        RoleInterface $role = null,
        ResourceInterface $resource = null,
        $privilege = null
    ) {
        if (null === $role || $role->getRoleId() !== $this->identity->getRoleId()) {
            return false;
        }

        if (null === $resource || 'blog' !== $resource->getResourceId()) {
            return false;
        }

        return $this->identity->getRoleId() === $this->content->getCreatorUsername();
    }
};

// Attach the assertion to all roles on the blog resource;
// custom assertions are provided as a fourth argument to allow().
$acl->allow(null, 'blog', null, $assertion);

$acl->isAllowed('mario', 'blog', 'edit'); // returns true if $mario created $content
```

The above creates a new assertion that will trigger for the "blog" resource when
a privilege we do not already know about is queried. In that particular case, if
the creator of our content is the same as the current user, it will return
`true`, allowing access!

By creating such assertions in-place with data retrieved at runtime, you can
achieve an incredible amount of flexibility for your ACLs.

## Wrapping up

zend-permissions-acl provides a huge amount of power, and the ability to provide
both role and resource inheritance can vastly simplify setup of complex ACLs.
Additionally, the privilege system provides much-needed granularity.

If you wanted to use ACLs in middleware, the usage is quite similar to
zend-permissions-rbac: inject your ACL instance in your middleware, retrieve
your user identity (and thus role) from the request, and perform queries against
the ACL using the current middleware or route as a resource, and either the HTTP
method or the domain action you will perform as the privilege.

The main difficulty with zend-permissions-acl is that there is no 1:1
relationship between a role and a privilege, which makes storing ACL information
in a database more complex. If you find yourself struggling with that fact, you
may want to use RBAC instead.

Hopefully these last few posts provide you with the information you need to
start adding authentication and authorization to _any_ PHP application you're
writing!

> ## Save the date!
>
> Want to learn more about Expressive and Zend Framework? What better location
> than ZendCon 2017! ZendCon will be hosted 23-26 October 2017 in Las Vegas,
> Nevada, USA. [Visit the ZendCon website for more
> information](http://www.zendcon.com).
