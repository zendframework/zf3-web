---
layout: post
title: REST Representations for Expressive
date: 2017-08-08T15:14:00-05:00
author: Matthew Weier O'Phinney
url_author: https://mwop.net/
permalink: /blog/2017-08-08-expressive-rest-representations.html
categories:
- blog
- apigility
- expressive
- api
- rest
- hal
- problem-details

---

We've been working towards our various Apigility on Expressive goals, and have
recently published two new components:

- [zend-problem-details](https://github.com/zendframework/zend-problem-details)
- [zend-expressive-hal](https://github.com/zendframework/zend-expressive-hal)

These components provide _response representations_ for APIs built with
[PSR-7](http://www.php-fig.org/psr/psr-7/) middleware. Specifically, they
provide:

- [Problem Details for HTTP APIs (RFC 7807)](https://tools.ietf.org/html/rfc7807)
- [Hypertext Application Language (HAL)](https://tools.ietf.org/html/draft-kelly-json-hal-08)

These two formats provide both JSON and XML representation options (the latter
through a [secondary proposal](https://tools.ietf.org/html/draft-michaud-xml-hal-01)).

## What's in a representation?

So you're developing an API!

What can clients expect when they make a request to your API? Will they get a
wall of text? or some sort of serialization? If it's a serialized format, which
ones do you support? And how is the data structured?

The typical answer will be, "we'll provide JSON responses." That answers the
serialization aspect, but not the _data structure_; for that, you might develop
and publish a schema for your end users, so they know how to parse the response.

But there may still be unanswered questions when you do so:

- How does the consumer know what actions can next be taken, or what resources
  might be related to the one requested?
- If the resource contains other entities, how can they identify which ones they
  can request separately, versus those that are just part of the data structure?

These and all of the previous are questions that a representation format
answers. A well-considered representation format will:

- Provide _links_ to the actions that may be performed next, as well as to
  related resources.
- Indicate which data elements represent other requestable resources.
- Be extensible, to allow representing arbitrary data.

I tend to think of representations as falling into two buckets:

- Representations of errors.
- Representations of application resources.

Errors need separate representation, as they are not _requestable_ on their own;
they are returned when something goes wrong, and need to provide enough detail
that the consumer can determine what they need to change in order to perform a
new request.

The Problem Details specification provides exactly this. As an example:

```json
{
    "type": "https://example.com/problems/rate-limit-exceeded",
    "title": "You have exceeded your API rate limit.",
    "detail": "You have hit your rate limit of 5000 requests per hour.",
    "requests_this_hour": 5025,
    "rate_limit": 5000,
    "rate_limit_reset": "2017-05-03T14:39-0500"
}
```

We chose Problem Details to standardize on when starting the Apigility project
as it has very few requirements, but can model any error easily. The ability to
link to documentation detailing general error types provides the ability to
communicate with your consumers about known errors and how to correct them.

Application resources generally should have their own schema, but having a
predictable structure for providing _relational links_ (answering the "what can
I do next" question) and embedding related resources can help those making
clients or those consuming your API to automate many of their processes.
Instead of having a list of URLs they can access, they can hit one resource,
and start following the composed links; when they present data, they can also
present controls for the embedded resources, making it simpler to make
requests to these other items.

HAL provides these details in a simple way: relational links are objects under
the `_links` element, and embedded resources are under the `_embedded` element;
all other data is represented as normal key/value pairs, allowing for arbitrary
nesting of structures. An example payload might look like the following:

```json
{
    "_links": {
        "self": { "href": "/api/books?page=7" },
        "first": { "href": "/api/books?page=1" },
        "prev": { "href": "/api/books?page=6" },
        "next": { "href": "/api/books?page=8" },
        "last": { "href": "/api/books?page=17" }
        "search": {
            "href": "/api/books?query={searchTerms}",
            "templated": true
        }
    },
    "_embedded": {
        "book": [
            {
                "_links": {
                    "self": { "href": "/api/books/1234" }
                }
                "id": 1234,
                "title": "Hitchhiker's Guide to the Galaxy",
                "author": "Adams, Douglas"
            },
            {
                "_links": {
                    "self": { "href": "/api/books/6789" }
                }
                "id": 6789,
                "title": "Ancillary Justice",
                "author": "Leckie, Ann"
            }
        ]
    },
    "_page": 7,
    "_per_page": 2,
    "_total": 33
}
```

The above provides controls to allow a consumer to navigate through a result
set, as well as to perform another search against the API. It provides data
about the result set, and also embeds a number of resources, with links so that
the consumer can make requests against those individually. Having links present
in the payloads means that if the URI scheme changes later, a well-written
client will be unaffected, _as it will follow the links delivered in response
payloads_ instead of hard-coding them. This allows our API to evolve, without
affecting the robustness of clients.

A number of other representation formats have become popular over the years,
including:

- [JSON API](http://jsonapi.org)
- [Collection+JSON](http://amundsen.com/media-types/collection/format/)
- [Siren](http://hyperschema.org/mediatypes/siren)

Each are powerful and flexible in their own right. We standardized on HAL for
Apigility originally as it was one of the first published specifications; we've
continued with it as it is a format that's both easy to generate as well as
parse, and extensible enough to answer the needs of most API representations.

## zend-problem-details

The package [zendframework/zend-problem-details](https://github.com/zendframework/zend-problem-details)
provides a Problem Details implementation for PHP, and specifically for
generating [PSR-7](http://www.php-fig.org/psr/psr-7/) responses. It provides a
multi-faceted approach to providing error details to your users.

First, you can compose the `ProblemDetailsResponseFactory` into your middleware,
and use it to generate and return your error responses:

```php
return $this->problemDetails->createResponse(
    $request,                                           // PSR-7 request
    422,                                                // HTTP status
    'Invalid data detected in book submission',         // Detail
    'Invalid book',                                     // Problem title
    'https://example.com/api/doc/errors/invalid-book',  // Problem type (URL to details)
    ['messages' => $validator->getMessages()]           // Additional data
);
```

> The request instance is passed to the factory to allow it to perform _content
> negotiation_; zend-problem-details uses the `Accept` header to determine whether
> to serve a JSON or an XML representation, defaulting to XML if it is unable to
> match to either format.

The above will generate a response like the following:

```http
HTTP/1.1 422 Unprocessable Entity
Content-Type: application/problem+json

{
  "status": 422,
  "title": "Invalid Book",
  "type": "https://example.com/api/doc/errors/invalid-book",
  "detail": "Invalid data detected in book submission",
  "messages": [
    "Missing title",
    "Missing author"
  ]
}
```

> `ProblemDetailsFactory` is agnostic of PSR-7 implementation, and allows you to
> inject a response prototype and stream factory during instantiation. By
> default, it uses zend-diactoros for these artifacts if none are provided.

Second, you can create a response from a caught exception or throwable:

```php
return $this->problemDetails->createResponseFromThrowable(
    $request,
    $throwable
);
```

Currently, the factory uses the exception message for the detail, and 4XX and
5XX exception codes for the status (defaulting to 500 for any other value).

> We are currently evaluating a proposal that would have caught exceptions
> generate a canned Problem Details response with a status of 500, so the above
> behavior may change in the future. If you want to guarantee the code and
> message are used, you can create custom exceptions, as outlined below.

Third, extending on the ability to create details from throwables, we provide a
custom exception interface, `ProblemDetailsException`. This interface defines
methods for pulling additional information to provide to a Problem Details
response:

```php
namespace Zend\ProblemDetails\Exception;

interface ProblemDetailsException
{
    public function getStatus() : int;
    public function getType() : string;
    public function getTitle() : string;
    public function getDetail() : string;
    public function getAdditionalData() : array;
}
```

If you throw an exception that implements this interface, the
`createResponseFromThrowable()` method shown above will pull data from these
methods in order to create the response. This allows you to define
domain-specific exceptions that can provide additional details when used in an
API context.

Finally, we also provide optional middleware, `ProblemDetailsMiddleware`, that
does the following:

- Registers an error handler that casts PHP errors in the current
  `error_reporting` bitmask to `ErrorException` instances.
- Wraps calls to the delegate in a try/catch block.
- Passes any caught throwables to the `createResponseFromThrowable()` factory in
  order to return Problem Details responses.

We recommend using custom exceptions and this middleware, as the combination
allows you to focus your efforts on the positive outcome paths within your
middleware.

### Using it in Expressive

When using Expressive, you can then compose the `ProblemDetailsMiddleware`
within route-specific pipelines, allowing you to have separate error handlers
for the API parts of your application:

```php
// In config/routes.php:

// Per route:
$app->get('/api/books', [
    Zend\ProblemDetails\ProblemDetailsMiddleware::class,
    Books\Action\ListBooksAction::class,
], 'books');
$app->post('/api/books', [
    Zend\ProblemDetails\ProblemDetailsMiddleware::class,
    Books\Action\CreateBookAction::class,
]);
```

Alternately, if all API endpoints have a common URI path prefix, register it as
path-segregated middleware:

```php
// In config/pipeline.php:

$app->pipe('/api', Zend\ProblemDetails\ProblemDetailsMiddleware::class);
```

These approaches allow you to deliver consistently structured, useful errors to
your API consumers.

## zend-expressive-hal

The package [zendframework/zend-expressive-hal](https://github.com/zendframework/zend-expressive-hal)
provides a HAL implementation for [PSR-7](http://www.php-fig.org/psr/psr-7/)
applications. Currently, it allows creating PSR-7 response payloads only; we may
consider parsing HAL requests at a future date, however.

zend-expressive-hal implements [PSR-13 (Link Definition
Interfaces)](http://www.php-fig.org/psr/psr-13/), and provides structures for:

- Defining relational links
- Defining HAL resources
- Composing relational links in HAL resources
- Embedding HAL resources in other HAL resources

These utilities can be used manually, without any other requirements:

```php
use Zend\Expressive\Hal\HalResource;
use Zend\Expressive\Hal\Link;

$author = new HalResource($authorDataArray);
$author = $author->withLink(
    new Link('self', '/authors/' .  $authorDataArray['id'])
);

$book = new HalResource($bookDataArray);
$book = $book->withLink(
    new Link('self', '/books/' .  $bookDataArray['id'])
);
$book = $book->embed('authors', [$author]);
```

> Both `Link` and `HalResource` are _immutable_; as such, if you wish to make
> iterative changes, you will need to re-assign the original value.

These clases allow you to model the data to return in your representation, but
what about returning a response based on them? To handle that, we have the
`HalResponseFactory`, which will generate a response from a resource provided to
it:

```php
return $halResponseFactory->createResponse($request, $book);
```

> Like the `ProblemDetailsFactory`, the `HalResponseFactory` is agnostic of
> PSR-7 implementation, and allows you to inject a response prototype and stream
> factory during instantiation.
>
> Also, it, too, uses content negotiation in order to determine whether a JSON
> or XML response should be generated.

The above might generate the following response:

```http
HTTP/1.1 200 OK
Content-Type: application/hal+json

{
  "_links": {
    "self": {"href": "/books/42"}
  },
  "id": 42
  "title": "The HitchHiker's Guide to the Galaxy",
  "_embedded": {
    "authors": [
      {
        "_links": {
          "self": {"href": "/author/12"}
        },
        "id": 12,
        "name": "Douglas Adams"
      }
    ]
  }
}
```

If your resources might be used in multiple API endpoints, you may find that
creating them manually everywhere you need them is a bit of a chore!

One of the most powerful pieces of zend-expressive-hal is that it provides tools
for mapping object types to how they should be represented. This is done via a
_metadata map_, which maps class types to zend-hydrator extractors for the
purpose of generating a representation. Additionally, we provide tools for
generating link URIs based on defined routes, which allows metadata to provide
dynamic link generation for generated resources.

I won't go into the architecture of how all this works, as there's a fair amount
of detail. In practice, what will generally happen is:

- You'll define a _metadata map_ in your application configuration, mapping your
  own classes to details on how to represent them.
- You'll compose a `Zend\Expressive\Hal\ResourceGenerator` (which will use a
  metadata map based on your configuration) and a `HalResponseFactory` in your
  middleware.
- You'll pass an object to the `ResourceGenerator` in order to produce a
  `HalResource`.
- You'll pass the generated `HalResource` to your `HalResponseFactory` to
  produce a response.

So, as an example, I might define the following metadata map configuration:

```php
namespace Books;

use Zend\Expressive\Hal\Metadata\MetadataMap;
use Zend\Expressive\Hal\Metadata\RouteBasedCollectionMetadata;
use Zend\Expressive\Hal\Metadata\RouteBasedResourceMetadata;
use Zend\Hydreator\ObjectProperty as ObjectPropertyHydrator;

class ConfigProvider
{
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
            MetadataMap::class => $this->getMetadataMap(),
        ];
    }

    public function getDependencies() : array
    {
        return [ /* ... */ ];
    }

    public function getMetadataMap() : array
    {
        return [
            [
                '__class__' => RouteBasedResourceMetadata::class,
                'resource_class' => Author::class,
                'route' => 'author',
                'extractor' => ObjectPropertyHydrator::class,
            ],
            [
                '__class__' => RouteBasedCollectionMetadata::class,
                'collection_class' => AuthorCollection::class,
                'collection_relation' => 'authors',
                'route' => 'authors',
            ],
            [
                '__class__' => RouteBasedResourceMetadata::class,
                'resource_class' => Book::class,
                'route' => 'book',
                'extractor' => ObjectPropertyHydrator::class,
            ],
            [
                '__class__' => RouteBasedCollectionMetadata::class,
                'collection_class' => BookCollection::class,
                'collection_relation' => 'books',
                'route' => 'books',
            ],
        ];
    }
}
```

The above defines metadata for authors and books, both as individual resources
as well as collections. This allows us to then embed an author as a property of
a book, and have it represented as an embedded resource!

From there, we could have middleware that composes both a `ResourceGenerator`
and a `HalResponseFactory` in order to produce representations:

```php
namespace Books\Action;

use Books\Repository;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Hal\HalResponseFactory;
use Zend\Expressive\Hal\ResourceGenerator;

class ListBooksAction implements MiddlewareInterface
{
    private $repository;
    private $resourceGenerator;
    private $responseFactory;

    public function __construct(
        Repository $repository,
        ResourceGenerator $resourceGenerator,
        HalResponseFactory $responseFactory
    ) {
        $this->repository = $repository;
        $this->resourceGenerator = $resourceGenerator;
        $this->responseFactory = $responseFactory;
    }

    public function process(
        ServerRequestInterface $request,
        DelegateInterface $delegate
    ) : ResponseInterface {
        /** @var \Books\BookCollection $books */
        $books = $this->repository->fetchAll();

        return $this->responseFactory->createResponse(
            $request,
            $this->resourceGenerator->fromObject($books)
        );
    }
}
```

When using zend-expressive-hal to generate your responses, the majority of your
middleware will look almost exactly like this!

We provide a number of other features in the package as well:

- You can define your own metadata types, and strategy classes for producing
  representations based on objects matching that metadata.
- You can specify custom mediatypes for your generated responses.
- You can provide your own link generation (useful if you're not using
  Expressive).
- You can provide your own JSON and XML renderers, if you want to vary the
  output for some reason (e.g., always adding specific links).

## Use Anywhere!

These two packages, while part of the Zend Framework and Expressive ecosystems,
can be used anywhere you use PSR-7 middleware. The Problem Details component
provides a factory for producing a PSR-7 Problem Details response, and
optionally middleware for automating reporting of errors. The HAL component
provides only a factory for producing a PSR-7 HAL response, and a number of
tools for modeling the data to return in that response.

As such, we encourage Slim, Lumen, and other PSR-7 framework users to consider
using these components in your API applications to provide standard, robust, and
extensible representations to your users!

For more details and examples, visit the docs for each component:

- [zend-problem-details documentation](https://docs.zendframework.com/zend-problem-details)
- [zend-expressive-hal documentation](https://docs.zendframework.com/zend-expressive-hal)
