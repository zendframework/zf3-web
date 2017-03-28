---
layout: post
title: Handling OPTIONS and HEAD Requests with Expressive
date: 2017-03-28T14:40:00-05:00
author: Matthew Weier O'Phinney
url_author: https://mwop.net
permalink: /blog/2017-03-28-expressive-options-head.html
categories:
- blog
- expressive
- php

---

In v1 releases of Expressive, if you did not define routes that included the
`OPTIONS` or `HEAD` HTTP request methods, routing would result in `404 Not
Found` statuses, even if a specified route matched the given URI. [RFC
7231](https://tools.ietf.org/html/rfc7231), however, states that both of these
request methods SHOULD work for a given resource URI, so long as it exists on
the server. This left users in a bit of a bind: if they wanted to comply with
the specification (which is often necessary to work correctly with HTTP client
software), they would need to either:

- inject additional routes for handling these methods, or
- overload existing middleware to also accept these methods.

In the case of a `HEAD` request, the specification indicates that the resulting
response should be identical to that of a `GET` request to the same URI, only
with no body content. This would mean having the same response headers.

In the case of an `OPTIONS` request, typically you would respond with a `200 OK`
response status, and at least an `Allow` header indicating what HTTP request
methods the resource allows.

Sounds like these could be automated, doesn't it?

In Expressive 2, we did!

## Handling HEAD requests

If you are using the v2 release of the Expressive skeleton, or have used the
`expressive-pipeline-from-config` tool to migrate your application to v2, then
you already have support for implicitly adding `HEAD` support to your routes. If
not, please go [read the documentation](https://docs.zendframework.com/zend-expressive/features/middleware/implicit-methods-middleware/#implicitheadmiddleware).

As noted in the documentation, the support is provided by
`Zend\Expressive\Middleware\ImplicitHeadMiddleware`, and it operates:

- If the request method is `HEAD`, AND
- the request composes a `RouteResult` attribute, AND
- the route result composes a `Route` instance, AND
- the route returns `true` for the `implicitHead()` method, THEN
- the middleware will return a response.

When the matched route supports the `GET` method, it will dispatch it, and then
inject the returned response with an empty body before returning it; this
preserves the original response headers, allowing it to operate per RFC 7231 as
described above. If `GET` is _not_ supported, it simply returns an empty
response.

What if you want to customize what happens when `HEAD` is called for a given
route?

That's easy: register custom middleware! As a simple, inline example:

```php
// In config/routes.php:

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\EmptyResponse;

$app->route(
    '/foo',
    new class implements MiddlewareInterface
    {
        public function process(ServerRequestInterface $request, DelegateInterface $delegate)
        {
            // Return a custom, empty response
            $response = new EmptyResponse(200, [
                'X-Foo' => 'Bar',
            ]);
        }
    },
    ['HEAD']
);
```

## Handling OPTIONS requests

Like `HEAD` requests above, if you're using Expressive 2, the middleware for
implicitly handling `OPTIONS` requests is already enabled; if not, 
please go [read the documentation](https://docs.zendframework.com/zend-expressive/features/middleware/implicit-methods-middleware/#implicitoptionsmiddleware).

`OPTIONS` requests are handled by `Zend\Expressive\Middleware\ImplicitOptionsMiddleware`,
which:

- If the request method is `OPTIONS`, AND
- the request composes a `RouteResult` attribute, AND
- the route result composes a `Route` instance, AND
- the route returns true for the `implicitOptions()` method, THEN
- the middleware will return a response with an `Allow` header indicating
  methods the route allows.

The Expressive contributors worked to ensure this is consistent across supported
router implementations; be aware, however, that if you are using a custom
router, it's possible that this may result in `Allow` headers that only contain
a subset of all allowed HTTP methods.

What happens if you want to provide a custom `OPTIONS` response? For example, a
number of prominent API developers suggest having `OPTIONS` payloads with usage
instructions, such as this:

```http
HTTP/1.1 200 OK
Allow: GET, POST
Content-Type: application/json

{
    "GET": {
        "query": {
            "page": "int; page of results to return",
            "per_page": "int; number of results to return per page"
        },
        "response": {
            "total": "Total number of items",
            "count": "Total number of items returned on this page",
            "_links": {
                "self": "URI to collection",
                "first": "URI to first page of results",
                "prev": "URI to previous page of results",
                "next": "URI to next page of results",
                "last": "URI to last page of results",
                "search": "URI template for searching"
            },
            "_embedded": {
                "books": [
                    "See ... for details"
                ]
            }
        }
    },
    "POST": {
        "data": {
            "title": "string; title of book",
            "author": "string; author of book",
            "info": "string; book description and notes"
        },
        "response": {
            "_links": {
                "self": "URI to book"
            },
            "id": "string; generated UUID for book",
            "title": "string; title of book",
            "author": "string; author of book",
            "info": "string; book description and notes"
        }
    }
}
```

The answer is the same as with `HEAD` requests: register a custom route!

```php
<?php
// In config/routes.php:

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

$app->route(
    '/books',
    new class implements MiddlewareInterface
    {
        public function process(ServerRequestInterface $request, DelegateInterface $delegate)
        {
            // Return a custom response
            $response = new JsonResponse([
                'GET': [
                    'query': [
                        'page': 'int; page of results to return',
                        'per_page': 'int; number of results to return per page',
                    ],
                    'response': [
                        'total': 'Total number of items',
                        'count': 'Total number of items returned on this page',
                        '_links': [
                            'self': 'URI to collection',
                            'first': 'URI to first page of results',
                            'prev': 'URI to previous page of results',
                            'next': 'URI to next page of results',
                            'last': 'URI to last page of results',
                            'search': 'URI template for searching',
                        ],
                        '_embedded': [
                            'books': [
                                'See ... for details',
                            ],
                        ],
                    ],
                ],
                'POST': [
                    'data': [
                        'title': 'string; title of book',
                        'author': 'string; author of book',
                        'info': 'string; book description and notes',
                    ],
                    'response': [
                        '_links': [
                            'self': 'URI to book',
                        ],
                        'id': 'string; generated UUID for book',
                        'title': 'string; title of book',
                        'author': 'string; author of book',
                        'info': 'string; book description and notes',
                    ],
                ],
            ], 200, ['Allow' => 'GET,POST']);
        }
    },
    ['OPTIONS']
);
```

## Final word

Obviously, you may not want to use inline classes as described above, but
hopefully with the above examples, you can begin to see the possibilities for
handling `HEAD` and `OPTIONS` requests in Expressive. The simplest option, which
will likely suffice for the majority of use cases, is now built-in to the
skeleton, and added by default when using the migration tools. For those other
cases where you need further customization, Expressive's routing capabilities
give you the flexibility and power to accomplish whatever you might need.

For more information on the built-in capabilities, [visit the
documentation](https://docs.zendframework.com/zend-expressive/features/middleware/implicit-methods-middleware/).
