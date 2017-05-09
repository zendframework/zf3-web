---
layout: post
title: Manage permissions with zend-permissions-rbac
date: 2017-04-27T15:15:30-05:00
author: Matthew Weier O'Phinney
url_author: https://mwop.net/
permalink: /blog/2017-04-27-zend-permissions-rbac.html
categories:
- blog
- components
- zend-permissions-rbac

---

In our [previous post](/blog/2017-04-26-authentication-middleware.html), we
covered _authentication_ of a user via Expressive middleware. In that post, we
indicated that we would later discuss _authorization_, which is the activity of
checking if an authenticated user has _permissions_ to perform a specific
action, from within the context of a middleware application.

Before we do that, however, we thought we'd introduce
[zend-permissions-rbac](https://docs.zendframework.com/zend-permissions-rbac/),
our lightweight role-based access control (RBAC) implementation.

## Installing zend-permissions-rbac

Just as you would any of our components, install zend-permissions-rbac via
Composer:

```bash
$ composer require zendframework/zend-permissions-rbac
```

The component has no requirements at this time other than a PHP version of at
least 5.5.

## Vocabulary

In RBAC systems, we have three primary items to track:

- the **RBAC** system composes zero or more _roles_.
- a **role** is granted zero or more _permissions_.
- we **assert** whether or not a _role_ is _granted_ a given _permission_.

zend-permissions-rbac supports role inheritance, even allowing a role to inherit
permissions from multiple other roles. This allows you to create some fairly
complex and fine-grained permissions schemes!

## Basics

As a basic example, we'll create an **RBAC** for a content-based website. Let's
start with a "guest" **role**, that only allows "read" permissions.

```php
use Zend\Permissions\Rbac\Rbac;
use Zend\Permissions\Rbac\Role;

// Create some roles
$guest= new Role('guest');
$guest->addPermission('read');

$rbac = new Rbac();
$rbac->addRole($guest);
```

We can then **assert** if a given role _is granted_ specific permissions:

```php
$rbac->isGranted('guest', 'read'); // true
$rbac->isGranted('guest', 'write'); // false
```

> ### Unknown roles
>
> One thing to note: if the role used with `isGranted()` does not exist, this
> method _raises an exception_, specifically a
> `Zend\Permissions\Rbac\Exception\InvalidArgumentException`, indicating the
> role could not be found.
>
> In many situations, this may not be what you want; you may want to handle
> non-existent roles gracefully. You could do this in a couple ways. First, you
> can test to see if the role exists _before_ you check the permissions, using
> `hasRole()`:
>
> ```php
> if (! $rbac->hasRole($foo)) {
>     // failed, due to missing role
> }
> if (! $rbac->isGranted($foo, $permission)) {
>     // failed, due to missing permissions
> }
> ```
>
> Alternately, wrap the `isGranted()` call in a try/catch block:
>
> ```php
> try {
>     if (! $rbac->isGranted($foo, $permission)) {
>         // failed, due to missing permissions
>     }
> } catch (RbacInvalidArgumentException $e) {
>     if (! strstr($e->getMessage(), 'could be found')) {
>         // failed, due to missing role
>     }
>
>     // some other error occured
>     throw $e;
> }
> ```
>
> Personally, I don't like to use exceptions for application flow, so I
> recommend the first solution. That said, in most cases, you will be working
> with a role instance that you've just added to the RBAC.

## Role inheritance

Let's say we want to build on the previous example, and create an "editor" role
that also incorporates the permissions of the "guest" role, and adds a "write"
permission.

You might be inclined to think of the "editor" as _inheriting_ from the "guest"
role &mdash; in other words, that it is a _descendent_ or _child_ of it.
However, in RBAC, inheritance works in the opposite direction: a _parent_
inherits all permissions of its _children_. As such, we'll create the role as
follows:

```php
$editor = new Role('editor');
$editor->addChild('guest');
$editor->addPermission('write');

$rbac->addRole($editor);

$rbac->isGranted('editor', 'write'); // true
$rbac->isGranted('editor', 'read');  // true
$rbac->isGranted('guest',  'write'); // false
```

Another role might be a "reviewer" who can "moderate" content:

```php
$reviewer = new Role('reviewer');
$reviewer->addChild('guest');
$reviewer->addPermission('moderate');

$rbac->addRole($reviewer);

$rbac->isGranted('reviewer', 'moderate'); // true
$rbac->isGranted('reviewer', 'write');    // false; editor only!
$rbac->isGranted('reviewer', 'read');     // true
$rbac->isGranted('guest',    'moderate'); // false
```

Let's create another, an "admin" who can do **all** of the above, but also has
permissions for "settings":

```php
$admin= new Role('admin');
$admin->addChild('editor');
$admin->addChild('reviewer');
$admin->addPermission('settings');

$rbac->addRole($admin);

$rbac->isGranted('admin',    'settings'); // true
$rbac->isGranted('admin',    'write');    // true
$rbac->isGranted('admin',    'moderate'); // true
$rbac->isGranted('admin',    'read');     // true
$rbac->isGranted('editor',   'settings'); // false
$rbac->isGranted('reviewer', 'settings'); // false
$rbac->isGranted('guest',    'write');    // false
```

As you can see, permissions lookups are _recursive_ and _collective_; the RBAC
examines _all_ children and each of their descendants as far down as it needs to
determine if a given permission is granted!

## Creating your RBAC

When should you create your RBAC, exactly? And should it contain all roles and
permissions?

In most cases, you will be validating a single user's permissions. What's
interesting about zend-permissions-rbac is that if you know that user's role,
the permissions they have been assigned, and any child roles (and their
permissions) to which the role belongs, you have everything you need. This means
that you can do most lookups on-the-fly.

As such, you will typically do the following:

- Create a finite set of well-known roles and their permissions as a global RBAC.
- Add roles (and optionally permissions) for the current user.
- Validate the current user against the RBAC.

As an example, let's say I have a user Mario who has the role "editor", and also
adds the permission "update". If our RBAC is already populated per the above
examples, I might do the following:

```php
$mario= new Role('mario');
$mario->addChild('editor');
$mario->addPermission('update');

$rbac->addRole($mario);

$rbac->isGranted($mario,   'settings'); // false; admin only!
$rbac->isGranted($mario,   'update');   // true; mario only!
$rbac->isGranted('editor', 'update');   // false; mario only!
$rbac->isGranted($mario,   'write');    // true; all editors
$rbac->isGranted($mario,   'read');     // true; all guests
```

## Assigning roles to users

When you have some sort of authentication system in place, it will return some
sort of _identity_ or _user_ instance generally. You will then need to map this
to RBAC roles. But how?

Hopefully, you can store role information wherever you persist your user
information. Since roles are essentially stored internally as strings by
zend-permissions-rbac, this means that you can store the user role as a discrete
datum with your user identity.

Once you have, you have a few options:

- Use the role directly from your identity when checking permissions: e.g.,
  `$rbac->isGranted($identity->getRole(), 'write')`
- Create a `Zend\Permissions\Rbac\Role` instance (or other concrete class) with
  the role fetched from the identity, and use that for permissions checks:
  `$rbac->isGranted(new Role($identity->getRole()), 'write')`
- Update your identity instance to implement
  `Zend\Permissions\Rbac\RoleInterface`, and pass it directly to permissions
  checks: `$rbac->isGranted($identity, 'write')`

This latter approach provides a nice solution, as it then also allows you to
store specific _permissions_ and/or _child roles_ as part of the user data.

The `RoleInterface` looks like the following:

```php
namespace Zend\Permissions\Rbac;

use RecursiveIterator;

interface RoleInterface extends RecursiveIterator
{
    /**
     * Get the name of the role.
     *
     * @return string
     */
    public function getName();

    /**
     * Add permission to the role.
     *
     * @param $name
     * @return RoleInterface
     */
    public function addPermission($name);

    /**
     * Checks if a permission exists for this role or any child roles.
     *
     * @param  string $name
     * @return bool
     */
    public function hasPermission($name);

    /**
     * Add a child.
     *
     * @param  RoleInterface|string $child
     * @return Role
     */
    public function addChild($child);

    /**
     * @param  RoleInterface $parent
     * @return RoleInterface
     */
    public function setParent($parent);

    /**
     * @return null|RoleInterface
     */
    public function getParent();
}
```

The `Zend\Permissions\Rbac\AbstractRole` contains basic implementations of most
methods of the interface, including logic for querying child permissions, so we
suggest inheriting from that if you can.

As an example, you could store the permissions as a comma-separated string and
the parent role as a string internally when creating your identity instance:

```php
use Zend\Permissions\Rbac\AbstractRole;
use Zend\Permissions\Rbac\RoleInterface;
use Zend\Permissions\Rbac\Role;

class Identity extends AbstractRole
{
    /**
     * @param string $username
     * @param string $role
     * @param array $permissions
     * @param array $childRoles
     */
    public function __construct(
        string $username,
        array $permissions = [],
        array $childRoles = []
    ) {
        // $name is defined in AbstractRole
        $this->name = $username;

        foreach ($this->permissions as $permission) {
            $this->addPermission($permission);
        }

        $childRoles = array_merge(['guest'], $childRoles);
        foreach ($this->childRoles as $childRole) {
            $this->addChild($childRole);
        }
    }
}
```

Assuming your authentication system uses a database table, and a lookup returns
an array-like row with the user information on a successful lookup, you might
then seed your identity instance as follows:

```php
$identity = new Identity(
    $row['username'],
    explode(',', $row['permissions']),
    explode(',', $row['roles'])
);
```

This approach allows you to assign pre-determined roles to individual users,
while also allowing you to add fine-grained, individual permissions!

## Custom assertions

Sometimes a static assertion is not enough.

As an example, we may want to implement a rule that the _creator_ of a content
item in our website always has rights to _edit_ the item. How would we implement
that with the above system?

zend-permissions-rbac allows you to do so via dynamic assertions. Such
assertions are classes that implement
`Zend\Permissions\Rbac\AssertionInterface`, which defines the single method
`public function assert(Rbac $rbac)`.

For the sake of this example, let's assume:

- The content item is represented as an object.
- The object has a method `getCreatorUsername()` that will return the same
  username as we might have in our custom identity from the previous example.

Because we have PHP 7 at our disposal, we'll create the assertion as an
anonymous class:

```php
use Zend\Permissions\Rbac\AssertionInterface;
use Zend\Permissions\Rbac\Rbac;
use Zend\Permissions\Rbac\RoleInterface;

$assertion = new class ($identity, $content) implements AssertionInterface {
    private $content;
    private $identity;

    public function __construct(RoleInterface $identity, $content)
    {
        $this->identity = $identity;
        $this->content = $content;
    }

    public function assert(Rbac $rbac)
    {
        return $this->identity->getName() === $this->content->getCreatorUsername();
    }
};

$rbac->isGranted($mario, 'edit', $assertion); // returns true if $mario created $content
```

This opens even more possibilities than inheritance!

## Summary

zend-permissions-rbac is quite simple to operate, but that simplicity hides a
great amount of flexibility and power; you can create incredibly fine-grained
permissions schemes for your applications using this component!

Next week, Enrico will cover using the component within a middleware stack; stay
tuned!

> ## Save the date!
>
> Want to learn more about Expressive and Zend Framework? What better location
> than ZendCon 2017! ZendCon will be hosted 23-26 October 2017 in Las Vegas,
> Nevada, USA. [Visit the ZendCon website for more
> information](http://www.zendcon.com).
