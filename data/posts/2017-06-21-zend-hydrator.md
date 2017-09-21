---
layout: post
title: Convert objects to arrays and back with zend-hydrator
date: 2017-06-21T08:45:00-05:00
author: Matthew Weier O'Phinney
url_author: https://mwop.net/
permalink: /blog/2017-06-21-zend-hydrator.html
categories:
- blog
- components
- zend-hydrator

---

APIs are all the rage these days, and a tremendous number of them are being
written in PHP. When APIs were first gaining popularity, this seemed like a
match made in heaven: query the database, pass the results to `json_encode()`,
and voilà! API payload! In reverse, it's `json_decode()`, pass the data to the
database, and done!

Modern day professional PHP, however, is skewing towards usage of value objects
and entities, but we're still creating APIs. How can we take these objects and
create our API response payloads? How can we take incoming data and transform it
into the domain objects we need?

Zend Framework's answer to that question is zend-hydrator. Hydrators can
_extract_ an associative array of data from an object, and _hydrate_ an object
from an associative array of data.

## Installation

As with our other components, you can install zend-hydrator by itself:

```bash
$ composer require zendframework/zend-hydrator
```

Out-of-the-box, it only requires zend-stdlib, which is used internally for
transforming iterators to associative arrays. However, there are a number of
other interesting, if optional, features that require other components:

- You can create an _aggregate_ hydrator where each hydrator is responsible for
  a subset of data. This requires zend-eventmanager.
- You can filter/normalize the keys/properties of data using _naming
  strategies_; these require zend-filter.
- You can map object types to hydrators, and delegate hydration of arbitrary
  objects using the `DelegatingHydrator`. This feature utilizes the provided
  `HydratorPluginManager`, which requires zend-servicemanager.

In our examples below, we'll be demonstrating naming strategies and the
delegating hydrator, so we will install the dependencies those need:

```bash
$ composer require zendframework/zend-filter zendframework/zend-servicemanager
```

## Objects to arrays and back again

Let's take the following class definition:

```php
namespace Acme;

class Book
{
    private $id;

    private $title;

    private $author;

    public function __construct(int $id, string $title, string $author)
    {
        $this->id = $id;
        $this->title = $title;
        $this->author = $author;
    }
}
```

What we have is a value object, with no way to publicly grab any given datum.
We now want to represent it in our API. How do we do that?

The answer is via reflection, and zend-hydrator provides a solution for that:

```php
use Acme\Book;
use Zend\Hydrator\Reflection as ReflectionHydrator;

$book = new Book(42, 'Hitchhiker\'s Guide to the Galaxy', 'Douglas Adams');

$hydrator = new ReflectionHydrator();
$data = $hydrator->extract($book);
```

We now have an array representation of our `Book` instance!

Let's say that somebody has just submitted a book via a web form or an API. We
have the values, but want to create a `Book` out of them.

```php
use Acme\Book;
use ReflectionClass;
use Zend\Hydrator\Reflection as ReflectionHydrator;

$hydrator = new ReflectionHydrator();
$book = $hydrator->hydrate(
    $incomingData,
    (new ReflectionClass(Book::class))->newInstanceWithoutConstructor()
);
```

And now we have a `Book` instance!

> The `newInstanceWithoutConstructor()` construct is necessary in this case
> because our class has required constructor arguments. Another possibility is
> to provide an already populated instance, and hope that the submitted data
> will overwrite all data in the class. Alternately, you can create classes that
> have optional constructor arguments.

Most of the time, it can be as simple as this: create an appropriate hydrator
instance, and use either `extract()` to get an array representation of the
object, or `hydrate()` to create an instance from an array of data.

We provide a number of standard implementations:

- `Zend\Hydrator\ArraySerializable` works with `ArrayObject`
  implementations. It will also hydrate any object implementing either the
  method `exchangeArray()` or `populate()`, and extract from any object
  implementing `getArrayCopy()`.
- `Zend\Hydrator\ClassMethods` will use setter and getter methods to populate
  and extract objects. It also understands `has*()` and `is*()` methods as
  getters.
- `Zend\Hydrator\ObjectProperty` will use public instance properties.
- `Zend\Hydrator\Reflection` can extract and populate instance properties of any
  visibility.

## Filtering values

Since a common rationale for extracting data from objects is to create payloads
for APIs, you may find there is data in your object you _do not_ want to
represent.

zend-hydrator provides a `Zend\Hydrator\Filter\FilterInterface` for
accomplishing this. Filters implement the following:

```php
namespace Zend\Hydrator\Filter;

interface FilterInterface
{
    /**
     * @param string $property
     * @return bool
     */
    public function filter($property);
}
```

If a filter returns a boolean `true`, the value is kept; otherwise, it is
omitted.

A `FilterComposite` implementation allows attaching multiple filters; each
property is then checked against each filter.  (This class also allows
attaching standard PHP callables for filters, instead of `FilterInterface`
implementations.) A `FilterEnabledInterface` allows a hydrator to indicate it
composes filters. Tying it together, all shipped hydrators inherit from a
common base that implements `FilterEnabledInterface` by composing a
`FilterComposite`, which means that you can use filters immediately in a
standard fashion.

As an example, let's say we have a `User` class that has a `password` property;
we clearly do not want to return the password in our payload, even if it is
properly hashed! Filters to the rescue!

```php
use Zend\Hydrator\ObjectProperty as ObjectPropertyHydrator;

$hydrator = new ObjectPropertyHydrator();
$hydrator->addFilter('password', function ($property) {
    return $property !== 'password';
});
$data = $hydrator->extract($user);
```

Some hydrators actually use filters internally in order to do their work. As an
example, the `ClassMethods` hydrator composes the following by default:

- `IsFilter`, to identify methods beginning with `is`, such as `isTransaction()`.
- `HasFilter`, to identify methods beginning with `has`, such as `hasAuthor()`.
- `GetFilter`, to identify methods beginning with `get`, such as `getTitle()`.
- `OptionalParametersFilter`, to ensure any given matched method can be executed
  without requiring any arguments.

This latter point brings up an interesting feature: since hydration runs each
potential property name through each filter, you may need to setup rules. For
example, with the `ClassMethods` hydrator, a given method name is valid if the
following condition is met:

```text
(matches "is" || matches "has" || matches "get") && matches "optional parameters"
```

As such, when calling `addFilter()`, you can specify an optional third argument:
a flag indicating whether to `OR` or `AND` the given filter (using the values
`FilterComposite::CONDITION_OR` or `FilterComposite::FILTER_AND`); the default
is to `OR` the new filter.

Filtering is very powerful and flexible. If you remember only two things about
filters:

- They only operate _during extraction_.
- They can only be used to determine what values to keep in the extracted data set.

## Strategies

What if you wanted to alter the values returned during extraction or hydration?
zend-hydrator provides these features via _strategies_.

A _strategy_ provides functionality both for extracting and hydrating a value,
and simply transforms it; think of strategies as normalization filters. Each
implements `Zend\Hydrator\Strategy\StrategyInterface`:

```php
namespace Zend\Hydrator\Strategy;

interface StrategyInterface
{
    public function extract($value;)
    public function hydrate($value;)
}
```

Like filters, a `StrategyEnabledInterface` allows a hydrator to indicate it
accepts strategies, and the `AbstractHydrator` implements this interface,
allowing you to use strategies out of the box with the shipped hydrators.

Using our previous `User` example, we could, instead of omitting the `password`
value, instead return a static `********` value; a strategy could allow us to do
that. Data submitted would be instead hashed using `password_hash()`:

```php
namespace Acme;

use Zend\Hydrator\Strategy\StrategyInterface;

class PasswordStrategy implements StrategyInterface
{
    public function extract($value)
    {
        return '********';
    }

    public function hydrate($value)
    {
        return password_hash($value);
    }
}
```

We would then extract our data as follows:

```php
use Acme\PasswordStrategy;
use Zend\Hydrator\ObjectProperty as ObjectPropertyHydrator;

$hydrator = new ObjectPropertyHydrator();
$hydrator->addStrategy('password', new PasswordStrategy());
$data = $hydrator->extract($user);
```

zend-hydrator ships with a number of really useful strategies for common data:

- `BooleanStrategy` will convert booleans into other values (such as `0` and
  `1`, or the strings `true` and `false`) and vice versa, according to a map you
  provide to the constructor.
- `ClosureStrategy` allows you to provide callbacks for each of extraction and
  hydration, allowing you to forego the need to create a custom strategy
  implementation.
- `DateTimeFormatterStrategy` will convert between strings and `DateTime`
  instances.
- `ExplodeStrategy` is a wrapper around `implode` and `explode()`, and expects a
  delimiter to its constructor.
- `StrategyChain` allows you to compose multiple strategies; the return value of
  each is passed as the value to the next, providing a filter chain.

## Filtering property names

We can now filter properties to omit from our representations, as well as filter
or normalize the values we ultimately want to represent. What about the property
names, though?

In PHP, we often use `camelCase` to represent properties, but `snake_case` is
typically more accepted for APIs. Additionally, what about when we use getters
for our values? We likely don't want to use the actual method name as the
property name!

For this reason, zend-hydrator provides _naming strategies_. These work just
like strategies, but instead of working on the value, they work on the property
name. Like both filters and strategies, an interface,
`NamingStrategyEnabledInterface`, allows a hydrator to indicate can accept
a naming strategy, and the `AbstractHydrator` implements that interface, to
allow out of the box usage of naming strategies on the shipped hydrators.

As an example, let's consider the following class:

```php
namespace Acme;

class Transaction
{
    public $isPublished;
    public $publishedOn;
    public $updatedOn;
}
```

Let's now extract an instance of that class:

```php
use Acme\Transaction;
use Zend\Hydrator\NamingStrategy\UnderscoreNamingStrategy;
use Zend\Hydrator\ObjectProperty as ObjectPropertyHydrator;

$hydrator = new ObjectPropertyHydrator();
$hydrator->setNamingStrategy(new UnderscoreNamingStrategy());
$data = $hydrator->extract($transaction);
```

The extracted data will now have the keys `is_published`, `published_on`, and
`updated_on`!

This is useful if you know all your properties will be camelCased, but what if
you have other needs? For instance, what if you want to rename `isPublished` to
`published` instead?

A `CompositeNamingStrategy` class allows you to do exactly that. It accepts an
associative array of object property names mapped to the naming strategy to use
with it. So, as an example:

```php
use Acme\Transaction;
use Zend\Hydrator\NamingStrategy\CompositeNamingStrategy;
use Zend\Hydrator\NamingStrategy\MapNamingStrategy;
use Zend\Hydrator\NamingStrategy\UnderscoreNamingStrategy;
use Zend\Hydrator\ObjectProperty as ObjectPropertyHydrator;

$underscoreNamingStrategy = new UnderscoreNamingStrategy();
$namingStrategy = new CompositeNamingStrategy([
    'isPublished' => new MapNamingStrategy(['published' => 'isPublished']),
    'publishedOn' => $underscoreNamingStrategy,
    'updatedOn'   => $underscoreNamingStrategy,
]);

$hydrator = new ObjectPropertyHydrator();
$hydrator->setNamingStrategy($namingStrategy);
$data = $hydrator->extract($transaction);
```

Our data will now have the keys `published`, `published_on`, and `updated_on`!

Unfortunately, if we try and hydrate using our `CompositeNamingStrategy`, we'll
run into issues; the `CompositeNamingStrategy` does not know how to map the
normalized, extracted property names to those the object accepts because it maps
a property name to the naming strategy. So, to fix that, we need to add the
reverse keys:

```php
$mapNamingStrategy = new MapNamingStrategy(['published' => 'isPublished']);
$underscoreNamingStrategy = new UnderscoreNamingStrategy();
$namingStrategy = new CompositeNamingStrategy([
    // Extraction:
    'isPublished'  => $mapNamingStrategy,
    'publishedOn'  => $underscoreNamingStrategy,
    'updatedOn'    => $underscoreNamingStrategy,

    // Hydration:
    'published'    => $mapNamingStrategy,
    'published_on' => $underscoreNamingStrategy,
    'updated_on'   => $underscoreNamingStrategy,
]);
```

## Delegation

Sometimes we want to compose a single hydrator, but don't know until runtime
what objects we'll be extracting or hydrating. A great example of this is when
using zend-db's `HydratingResultSet`, where the hydrator may vary based on the
table from which we pull values. Other times, we may want to use the same basic
hydrator type, but compose different filters, strategies, or naming strategies
based on the object we wish to hydrate or extract.

To accommodate these scenarios, we have two features. The first is
`Zend\Hydrator\HydratorPluginManager`. This is a specialized
`Zend\ServiceManager\AbstractPluginManager` for retrieving different hydrator
instances. When used in zend-mvc or Expressive applications, it can be
configured via the `hydrators` configuration key, which uses the semantics for
zend-servicemanager, and maps the service to `HydratorManager`.

As an example, we could have the following configuration:

```php
return [
    'hydrators' => [
        'factories' => [
            'Acme\BookHydrator' => \Acme\BookHydratorFactory::class,
            'Acme\AuthorHydrator' => \Acme\AuthorHydratorFactory::class,
        ],
    ],
];
```

> ### Manually configuring the HydratorPluginManager
>
> You can also use the `HydratorPluginManager` programmatically:
>
> ```php
> $hydrators = new HydratorPluginManager();
> $hydrators->setFactory('Acme\BookHydrator', \Acme\BookHydratorFactory::class);
> $hydrators->setFactory('Acme\AuthorHydrator', \Acme\AuthorHydratorFactory::class);
> ```

The factories might create standard hydrator instances, but configure them
differently:

```php
namespace Acme;

use Psr\Container\ContainerInterface;
use Zend\Hydrator\ObjectProperty;
use Zend\Hydrator\NamingStrategy\CompositeNamingStrategy;
use Zend\Hydrator\NamingStrategy\UnderscoreNamingStrategy;
use Zend\Hydrator\Strategy\DateTimeFormatterStrategy;

class BookHydratorFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $hydrator = new ObjectProperty();
        $hydrator->addFilter('isbn', function ($property) {
            return $property !== 'isbn';
        });
        $hydrator->setNamingStrategy(new CompositeNamingStrategy([
            'publishedOn' => new UnderscoreNamingStrategy(),
        ]));
        $hydrator->setStrategy(new CompositeNamingStrategy([
            'published_on' => new DateTimeFormatterStrategy(),
        ]));
        return $hydrator;
    }
}

class AuthorHydratorFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $hydrator = new ObjectProperty();
        $hydrator->setNamingStrategy(new UnderscoreNamingStrategy());
        return $hydrator;
    }
}
```

You could then compose the `HydratorManager` service in your own class, and pull
these hydrators in order to extract or hydrate instances:

```php
$bookData = $hydrators->get('Acme\BookHydrator')->extract($book);
$authorData = $hydrators->get('Acme\AuthorHydrator')->extract($author);
```

The `DelegatingHydrator` works by composing a `HydratorPluginManager` instance,
but has an additional semantic: it uses the _class name of the object it is
extracting_, or the object type to hydrate, as the _service name_ to pull from
the `HydratorPluginManager`. As such, we would change our configuration of the
hydrators as follows:

```php
return [
    'hydrators' => [
        'factories' => [
            \Acme\Book::class => \Acme\BookHydratorFactory::class,
            \Acme\Author::class => \Acme\AuthorHydratorFactory::class,
        ],
    ],
];
```

Additionally, we need to tell our application about the `DelegatingHydrator`:

```php
// zend-mvc applications:
return [
    'service_manager' => [
        'factories' => [
            \Zend\Hydrator\DelegatingHydrator::class => \Zend\Hydrator\DelegatingHydratorFactory::class
        ]
    ],
];

// Expressive applications
return [
    'dependencies' => [
        'factories' => [
            \Zend\Hydrator\DelegatingHydrator::class => \Zend\Hydrator\DelegatingHydratorFactory::class
        ]
    ],
];
```

> ### Manually creating the DelegatingHydrator
>
> You can instantiate the `DelegatingHydrator` manually; when you do, you pass
> it the `HydratorPluginManager instance.
>
> ```php
> use Zend\Hydrator\DelegatingHydrator;
> use Zend\Hydrator\HydratorPluginManager;
>
> $hydrators = new HydratorPluginManager();
> // ... configure the plugin manager ...
> $hydrator = new DelegatingHydrator($hydrators);
> ```
>
> Technically speaking, the `DelegatingHydrator` can accept any [PSR-11](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-11-container.md)
> container to its constructor.

From there, we can inject the `DelegatingHydrator` into any of our own classes,
and use it to extract or hydrate objects:

```php
$bookData = $hydrator->extract($book);
$authorData = $hydrator->extract($author);
```

This feature can be quite powerful, as it allows you to create the hydration and
extraction "recipes" for all of your objects within their own factories,
ensuring that anywhere you need them, they operate exactly the same. It also
means that for testing purposes, you can simply mock the `HydratorInterface` (or
its parents, `ExtractionInterface` and `HydrationInterface`) instead of
composing a concrete instance.

## Other features

While we've tried to cover the majority of the functionality zend-hydrator
provides in this article, it has a number of other useful features:

- The `AggregateHydrator` allows you to handle complex objects that implement
  multiple common interfaces and/or have nested instances composed; it even
  exposes events you can listen to during each of extraction and hydration. You
  can [read more about it in the documentation](https://docs.zendframework.com/zend-hydrator/aggregate/).
- You can write objects that provide and expose their own filters by
  implementing the `Zend\Hydrator\Filter\FilterProviderInterface`.
- You can hydrate or extract arrays of objects by implementing
  `Zend\Hydrator\Iterator\HydratingIteratorInterface`.

The component can be seen in use in a number of places: zend-db provides a
`HydratingResultSet` that leverage the `HydratorPluginManager` in order to
hydrate objects pulled from a database. Apigility uses the feature to extract
data for Hypertext Application Language (HAL) payloads. We've even seen
developers creating custom ORMs for their application using the feature!

What can zend-hydrator help _you_ do today?

> ## Save the date!
>
> Want to learn more about Expressive and Zend Framework? What better location
> than ZendCon 2017! ZendCon will be hosted 23-26 October 2017 in Las Vegas,
> Nevada, USA. [Visit the ZendCon website for more
> information](http://www.zendcon.com).
