---
layout: post
title: Apigility on Expressive Update for 2017-07-13
date: 2017-07-13T18:30:00-05:00
author: Matthew Weier O'Phinney
url_author: https://mwop.net/
permalink: /blog/2017-07-13-apigility-on-expressive-update.html
categories:
- blog
- apigility
- expressive
- api
- rest
- hal
- problem-details

---

We've been working on the Apigility on Expressive initiative for a couple months
now, and have a bit of progress to report.

First, if you're unfamiliar with the initiative, please head over and [read the
RFC](https://github.com/zendframework/maintainers/issues/11).

In this post, we'll discuss what's done and ready to review, and what pieces are
in the works.

## Ready to review

Two pieces are currently ready to review:

- [Problem Details support](https://github.com/weierophinney/problem-details)
- [Hypertext Application Language (HAL) support](https://github.com/weierophinney/hal)

These two provide us the _representations_ that will be returned by your API.
Problem Details is used to describe API errors &mdash; whether those are due to
the client sending bad information, or server-side errors that occur. HAL is
used to provide your API payloads to the client.

### Problem Details

- [Request for Comments](https://discourse.zendframework.com/t/feedback-on-problem-details-module/107)
- Install: `composer require weierophinney/problem-details`
- [Source Code](https://github.com/weierophinney/problem-details)
- [Documentation](https://weierophinney.github.io/problem-details)

There are three facets to the proposed implementation:

- We provide a `ProblemDetailsResponseFactory` for you to compose in middleware.
  When you encounter an error condition, you can use it to generate and return a
  Problem Details response.  It has built-in content negotiation, and will
  return either a JSON or XML response based on the `Accept` client request
  header.

- We provide a custom exception interface, `ProblemDetailsException`, which you
  can implement that defines a number of getter methods for retrieving
  information to use when building a Problem Details response. (We also provide
  a trait, `CommonProblemDetailsException`, that implements the various getters,
  allowing you to focus on constructors.)

- We provide `ProblemDetailsMiddleware`, which acts as error handling
  middleware, catching exceptions and PHP errors and turning them into Problem
  Details responses via the `ProblemDetailsResponseFactory`. If you throw a
  `ProblemDetailsException`, this middleware will pull data from it to fully
  populate the error details!

We feel these three faculties allow a great deal of flexibility in how you
handle errors for your APIs.

### HAL

- [Request for Comments](https://discourse.zendframework.com/t/rfc-hal-support-for-apigility-on-expressive/139)
- Install: `composer require weierophinney/hal`
- [Source Code](https://github.com/weierophinney/hal)
- [Documentation](https://weierophinney.github.io/hal)

Our HAL implementation has several facets:

- Low-level value objects representing relational links and HAL resources. These
  can be created manually, and independently of any other ZF components.

- A `LinkGenerator` that uses a PSR-7 request instance and a composed
  `UrlGenerator` to allow creating links that reference application routes.

- Renderers for both JSON and XML. Each accepts a HAL resource, with its
  relational links, and produces the serialized version.

- A `ResourceGenerator` that maps an object to related metadata, and the related
  metadata to a strategy for creating the HAL resource. The shipped metadata and
  strategies use zend-hydrator for extracting data from objects, and
  zend-paginator awareness for producing pagination relational links.

- A `HalResponseFactory` for rendering resources and returning PSR-7 responses.
  It has built-in content negotiation to allow producing a response with the
  correct format.

While the library allows developers to manually create resources and links, the
real power comes from the ability to pass objects directly to the
`ResourceGenerator` in order to create a fully populated HAL resource with its
self-relational link; this vastly reduces boilerplate in middleware.

### Examples

The following demonstrates how you might use the two features together within
middleware to return responses.

First, we have some configuration for the metadata map that tells it how we want
to represent our objects:

```php
// In Books\ConfigProvider, or a config/autoload/*.global.php file:

use Books\Book;
use Books\BookCollection;
use Hal\Metadata\MetadataMap;
use Hal\Metadata\RouteBasedCollectionMetadata;
use Hal\Metadata\RouteBasedResourceMetadata;
use Zend\Hydrator\ObjectProperty as ObjectPropertyHydrator;

MetadataMap::class => [
    [
          [
              '__class__' => RouteBasedResourceMetadata::class,
              'resource_class' => Book::class,
              'route' => 'book',
              'extractor' => ObjectPropertyHydrator::class,
          ],
          [
              '__class__' => RouteBasedCollectionMetadata::class,
              'collection_class' => BookCollection::class,
              'collection_relation' => 'book',
              'route' => 'books',
          ],
    ],
],
```

`Books\Book` is a value object with public properties. `Books\BookCollection`
extends `Zend\Paginator\Paginator`, allowing a paginated collection.

We will also assume we have the following routes defined:

- **book** will map to `/books/{id}`
- **books** will map to `/books`

Finally, we get to our middleware. It assumes a `Books\Repository`, which is
simply a class that accesses our persistent storage.

```php
namespace Books;

use Hal\HalResponseFactory;
use Hal\ResourceGenerator;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use ProblemDetails\ProblemDetailsResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Helper\ServerUrlHelper;
use Zend\Expressive\Helper\UrlHelper;

class BookMiddleware implements MiddlewareInterface
{
    /** @var ProblemDetailsResponseFactory */
    private $problemDetails;

    /** @var Repository */
    private $repository;

    /** @var ResourceGenerator */
    private $resourceGenerator;

    /** @var HalResponseFactory **/
    private $responseFactory;

    public function __construct(
        Repository $repository,
        ResourceGenerator $resourceGenerator,
        HalResponseFactory $responseFactory,
        ProblemDetailsResponseFactory $problemDetails
    ) {
        $this->repository = $repository;
        $this->resourceGenerator = $resourceGenerator;
        $this->responseFactory = $responseFactory;
        $this->problemDetails = $problemDetails;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $id = $request->getAttribute('id', false);

        if (false === $id) {
            // Return a problem details response!
            return $this->problemDetails->createResponse(
                $request,
                400,
                'Missing book identifier',
                'Client Error',
                'book.id'
            );
        }

        $user = $request->getAttribute('user');

        try {
            $book = $this->repository->fetch($id, $user);
        } catch (Exception\BookNotFoundException $e) {
            // Return a problem details response!
            return $this->problemDetails->createResponse(
                $request,
                404,
                'Book not found',
                'Not Found',
                'book.not_found',
                [ 'book_id' => $id]
            );
        }

        // Create the resource
        $resource = $this->resourceGenerator->fromObject($book, $request);

        // Add another relational link
        $resource->withLink($this->resourceGenerator->getLinkGenerator()->templatedFromRoute(
            'search',
            $request,
            'books',
            [],
            ['query' => '{searchTerms}']
        ));

        // Return a response with the accepted representation
        return $this->responseFactory->createResponse($request, $resource);
    }
}
```

A different approach we could take would be to have our exceptions implement
`ProblemDetailsException`, and either throw them directly, or simply not catch
them. We would then register the `ProblemDetailsMiddleware` within our routed
middleware:

```php
$app->get('/books/{id}', [
    \ProblemDetails\ProblemDetailsMiddleware::class,
    \Books\BookMiddleware::class,
], 'book');
```

The approaches allow our middleware to focus primarily on gathering input,
calling our model, and then preparing a response.

## In the works

While these two are ready to review, we also have a number of other modules in
the works, and likely ready to review in the next few weeks:

- Authentication (by Enrico Zimuel)
- Authorization (by Enrico Zimuel)
- OAuth2 (by Julien Guittard)

The content negotiation module scope has decreased; since the proposed Problem
Details and HAL modules have negotiation built-in, we only need to focus on the
problem of negotiating _incoming data_. As such, we can likely tackle this
quickly as well.

## Help out!

We'd love for you to help out. You can do so by reviewing the linked RFCs, as
well as trying out the current code in your projects and reporting issues or
proposing improvements.

We're excited to start building full-fledged, featureful REST APIs with
Expressive!
