---
layout: post
title: Error Handling in Expressive
date: 2017-03-23T16:00:00-05:00
author: Matthew Weier O'Phinney
url_author: https://mwop.net
permalink: /blog/2017-03-23-expressive-error-handling.html
categories:
- blog
- expressive
- php

---

One of the big improvements in Expressive 2 is how error handling is approached.
While the [error handling documentation](https://docs.zendframework.com/zend-expressive/features/error-handling/)
covers the feature in detail, more examples are never a bad thing!

## Our scenario

For our example, we'll create an API resource that returns a list of books read.
Being an API, we want to return JSON; this is true even when we want to present
error details. Our challenge, then, will be to add error handling that presents
JSON error details when the API is invoked &mdash; but use the existing error
handling otherwise.

## The middleware

The middleware looks like the following:

```php
// In src/Acme/BooksRead/ListBooksRead.php:

namespace Acme\BooksRead;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use PDO;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class ListBooksRead implements MiddlewareInterface
{
    const SORT_ALLOWED = [
        'author',
        'date',
        'title',
    ];

    const SORT_DIR_ALLOWED = [
        'ASC',
        'DESC',
    ];

    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $query   = $request->getQueryParams();
        $page    = $this->validatePageOrPerPage((int) ($query['page'] ?? 1));
        $perPage = $this->validatePageOrPerPage((int) ($query['per_page'] ?? 25));
        $sort    = $this->validateSort($query['sort'] ?? 'date');
        $sortDir = $this->validateSortDirection($query['sort_direction'] ?? 'DESC');

        $offset = ($page - 1) * $perPage;

        $statement = $pdo->prepare(sprintf(
            'SELECT * FROM books_read ORDER BY %s %s LIMIT %d OFFSET %d',
            $sort,
            $sortDir,
            $perPage,
            $offset
        ));

        try {
            $statement->execute([]);
        } catch (PDOException $e) {
            throw Exception\ServerError::create(
                'Database error occurred',
                sprintf('A database error occurred: %s', $e->getMessage()),
                ['trace' => $e->getTrace()]
            );
        }

        $books = $statement->fetchAll(PDO::FETCH_ASSOC);

        return new JsonResponse(['books' => $books]);
    }

    private function validatePageOrPerPage($value, $param)
    {
        if ($value > 1) {
            return $value;
        }

        throw Exception\InvalidRequest::create(
            sprintf('Invalid %s value specified', $param),
            sprintf('The %s specified must be an integer greater than 1', $param)
        );
    }

    private function validateSort(string $sort)
    {
        if (in_array($sort, self::SORT_ALLOWED, true)) {
            return $sort;
        }

        throw Exception\InvalidRequest::create(
            'Invalid sort type specified',
            sprintf(
                'The sort type specified must be one of [ %s ]',
                implode(', ', self::SORT_ALLOWED)
            )
        );
    }

    private function validateSortDirection(string $direction)
    {
        if (in_array($direction, self::SORT_DIR_ALLOWED, true)) {
            return $direction;
        }

        throw Exception\InvalidRequest::create(
            'Invalid sort direction specified',
            sprintf(
                'The sort direction specified must be one of [ %s ]',
                implode(', ', self::SORT_DIR_ALLOWED)
            )
        );
    }
}
```

You'll notice that this middleware throws exceptions for error handling, and
uses some custom exception types. Let's examine those next.

### The exceptions

Our API will have custom exceptions. In order to provide _useful_ details to our
users, we'll have our exceptions compose additional details that we can report.
As such, we'll have a special interface for our API exceptions that exposes the
custom details.

We'll also define a few specific types. Since much of the work will be the same
between these types, we'll use a trait to define the common code, and compose
that into each.

```php
// In src/Acme/BooksRead/Exception/MiddlewareException.php:

namespace Acme\BooksRead\Exception;

interface MiddlewareException
{
    public static function create() : MiddlewareException;
    public function getStatusCode() : int;
    public function getType() : string;
    public function getTitle() : string;
    public function getDescription() : string;
    public function getAdditionalData() : array;
}
```

```php
// In src/Acme/BooksRead/Exception/MiddlewareExceptionTrait.php:

namespace Acme\BooksRead\Exception;

trait MiddlewareExceptionTrait
{
    private $statusCode;
    private $title;
    private $description;
    private $additionalData = [];

    public function getStatusCode() : int
    {
        return $this->statusCode;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function getAdditionalData() : array
    {
        return $this->additionalData;
    }
}
```

```php
// In src/Acme/BooksRead/Exception/ServerError.php:

namespace Acme\BooksRead\Exception;

use RuntimeException;

class ServerError extends RuntimeException implements MiddlewareException
{
    use MiddlewareExceptionTrait;

    public static function create(string $title, string $description, array $additionalData = [])
    {
        $e = new self($description, 500);
        $e->statusCode = 500;
        $e->title = $title;
        $e->additionalData = $additionalData;
        return $e;
    }

    public function getType() : string
    {
        return 'https://example.com/api/problems/server-error';
    }
}
```

```php
// In src/Acme/BooksRead/Exception/InvalidRequest.php:

namespace Acme\BooksRead\Exception;

use RuntimeException;

class InvalidRequest extends RuntimeException implements MiddlewareException
{
    use MiddlewareExceptionTrait;

    public static function create(string $title, string $description, array $additionalData = [])
    {
        $e = new self($description, 400);
        $e->statusCode = 400;
        $e->title = $title;
        $e->additionalData = $additionalData;
        return $e;
    }

    public function getType() : string
    {
        return 'https://example.com/api/problems/invalid-request';
    }
}
```

These specialized exception types have additional methods for retrieving
additional data. Furthermore, they set default exception codes, which may be
repurposed as status codes.

## A Problem Details error handler

What we want to have happen is for our API to return data in [Problem
Details](https://tools.ietf.org/html/rfc7807) format.

To accomplish this, we'll create a new middleware that will catch our
domain-specific exception type in order to create an appropriate response for
us.

```php
// In src/Acme/BooksRead/ProblemDetailsMiddleware.php:

namespace Acme\BooksRead;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Zend\Diactoros\Response\JsonResponse;

class ProblemDetailsMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        try {
            $response = $delegate->process($request);
            return $response;
        } catch (Exception\MiddlewareException $e) {
            // caught; we'll handle it following the try/catch block
        } catch (Throwable $e) {
            throw $e;
        }

        $problem = [
            'type'   => $e->getType(),
            'title'  => $e->getTitle(),
            'detail' => $e->getDescription(),
        ];
        $problem = array_merge($e->getAdditionalData(), $problem);

        return new JsonResponse($problem, $e->getStatusCode(), [
            'Content-Type' => 'application/problem+json',
        ]);
    }
}
```

This middleware always delegates processing of the request, but does so in a
try/catch block. If it catches our special `MiddlewareException`, it will
process it; otherwise, it re-throws the caught exception, to allow middleware in
an outer layer to handle it.

## Composing the error handler

Last week, we blogged about [nested middleware pipelines](/blog/2017-03-15-nested-middleware-in-expressive.html).
We'll use a similar technique here.

Assuming we have already defined a factory for our `ListBooksRead` middleware,
we have a few options. First, we could compose this error handler in a
middleware pipeline within our routing configuration:

```php
// In config/routes.php:

$app->get('/api/books-read', [
    \Acme\BooksRead\ProblemDetailsMiddleware::class,
    \Acme\BooksRead\ListBooksRead::class,
], 'api.books-read')
```

If there are other concerns &mdash; such as authentication, authorization,
content negotiation, etc. &mdash; you may want to instead create a delegator
factory; this can then be re-used for other API resources that need the same set
of middleware. As an example:

```php
// In src/Acme/BooksRead/ApiMiddlewareDelegatorFactory.php:

namespace Acme\BooksRead;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Application;
use Zend\Expressive\Router\RouterInterface;

class ApiMiddlewareDelegatorFactory
{
    public function __invoke(ContainerInterface $container, $name, callable $callback)
    {
        $apiPipeline = new Application(
            $container->get(RouterInterface::class),
            $container
        );

        $apiPipeline->pipe(ProblemDetailsMiddleware::class);
        // ..and pipe other middleware as necessary...

        $apiPipeline->pipe($callback());

        return $apiPipeline;
    }
}
```

The above would then be registered as a delegator with your `ListBooksRead`
service:

```php
// In Acme\BooksRead\ConfigProvider, or any config/autoload/*.global.php:

return [
    'dependencies' => [
        'delegators' => [
            \Acme\BooksRead\ListBooksRead::class => [
                \Acme\BooksRead\ApiMiddlewareDelegatorFactory::class,
            ],
        ],
    ]
];
```

## End result

Once you have created the pipeline, you should get some nice errors:

```http
HTTP/1.1 400 Client Error
Content-Type: application/problem+json

{
  "type": "https://example.com/api/problems/invalid-request",
  "title": "Invalid sort direction specified",
  "detail": "The sort direction specified must be one of [ ASC, DESC ]"
}
```

This approach to error handling allows you to be as granular or as generic as
you like with regards to how errors are handled. The shipped error handler takes
an all-or-nothing approach, handling both PHP errors and exceptions/throwables,
but treating them all the same. By sprinkling more specific error handlers into
your routed middleware pipelines, you can have more control over how your
application behaves, based on the context in which it executes.
