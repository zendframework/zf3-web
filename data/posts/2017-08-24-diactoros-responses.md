---
layout: post
title: Specialized Response Implementations in Diactoros
date: 2017-08-24T11:24:00-05:00
author: Matthew Weier O'Phinney
url_author: https://mwop.net/
permalink: /blog/2017-08-24-diactoros-responses.html
categories:
- blog
- psr7
- diactoros
- api

---

When writing [PSR-7](http://www.php-fig.org/psr/psr-7/) middleware, at some
point you'll need to return a response.

Maybe you'll be returning an empty response, indicating something along the
lines of successful deletion of a resource. Maybe you need to return some HTML,
or JSON, or just plain text. Maybe you need to indicate a redirect.

But here's the problem: a _generic_ response typically has a very _generic_
constructor. Take, for example, `Zend\Diactoros\Response`:

```php
public function __construct(
    $body = 'php://memory',
    $status = 200,
    array $headers = []
)
```

`$body` in this signature allows either a `Psr\Http\Message\StreamInterface`
instance, a PHP resource, or a string identifying a PHP stream. This means that
it's not terribly easy to create even a simple HTML response!

> To be fair, there are good reasons for a generic constructor: it allows
> setting the initial state in such a way that you'll have a fully populated
> instance immediately. However, the means for doing so, in order to be
> generic, leads to convoluted code for most consumers.

Fortunately, Diactoros provides a number of convenience implementations to help
simplify the most common use cases.

## EmptyResponse

The standard response from an API for a successful deletion is generally a `204
No Content`. Sites emitting webhook payloads often expect a `202 Accepted` with
no content. Many APIs that allow creation of resources will return a `201
Created`; these may or may not have content, depending on implementation, with
some being empty, but returning a `Location` header with the URI of the newly
created resource.

Clearly, in such cases, if you don't need content, why would you be bothered to
create a stream? To answer this, we have
`Zend\Diactoros\Response\EmptyResponse`, with the following constructor:

```php
public function __construct($status = 204, array $headers = [])
```

So, a `DELETE` endpoint might return this on success:

```php
return new EmptyResponse();
```

A webhook endpoint might do this:

```php
return new EmptyResponse(StatusCodeInterface::STATUS_ACCEPTED);
```

An API that just created a resource might do the following:

```php
return new EmptyResponse(
    StatusCodeInterface::STATUS_CREATED,
    ['Location' => $resourceUri]
);
```

## RedirectResponse

Redirects are common within web applications. We may want to redirect a user to
a login page if they are not currently logged in; we may have changed where some
of our content is located, and redirect users requesting the old URIs; etc.

`Zend\Diactoros\Response\RedirectResponse` provides a simple way to create and
return a response indicating an HTTP redirect. The signature is:

```php
public function __construct($uri, $status = 302, array $headers = [])
```

where `$uri` may be either a string URI, or a `Psr\Http\Message\UriInterface`
instance. This value will then be used to seed a `Location` HTTP header.

```php
return new RedirectResponse('/login');
```

You'll note that the `$status` defaults to 302. If you want to set a permanent
redirect, pass `301` for that argument:

```php
return new RedirectResponse('/archives', 301);

// or, using fig/http-message-util:
return new RedirectResponse('/archives', StatusCodeInterface::STATUS_PERMANENT_REDIRECT);
```

Sometimes you may want to set an header as well; do that by passing the third
argument, an array of headers to provide:

```php
return new RedirectResponse(
    '/login',
    StatusCodeInterface::STATUS_TEMPORARY_REDIRECT,
    ['X-ORIGINAL_URI' =>  $uri->getPath()]
);
```

## TextResponse

Sometimes you just want to return some text, whether it's plain text, XML, YAML,
etc. When doing that, taking the extra step to create a stream feels like
overhead:

```php
$stream = new Stream('php://temp', 'wb+');
$stream->write($content);
```

To simplify this, we offer `Zend\Diactoros\Response\TextResponse`, with the
following signature:

```php
public function __construct($text, $status = 200, array $headers = [])
```

By default, it will use a `Content-Type` of `text/plain`, which means you'll
often need to supply a `Content-Type` header with this response.

Let's return some plain text:

```php
return new TextResponse('Hello, world!');
```

Now, let's try returning a Problem Details XML response:

```php
return new TextResponse(
    $xmlPayload,
    StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
    ['Content-Type' => 'application/problem+xml']
);
```

If you have some textual content, this is the response for you.

## HtmlResponse

The most common response from web applications is HTML. If you're returning
HTML, even the `TextResponse` may seem a bit much, as you're forced to provide
the `Content-Type` header. To answer that, we provide
`Zend\Diactoros\Response\HtmlResponse`, which is exactly the same as
`TextResponse`, but with a default `Content-Type` header specifying
`text/html; charset=utf-8` instead.

As an example:

```php
return new HtmlResponse($renderer->render($template, $view));
```

## JsonResponse

For web APIs, JSON is generally the _lingua franca_. Within PHP, this generally
means passing an array or object to `json_encode()`, and supplying a
`Content-Type` header of `application/json` or `application/{type}+json`, where
`{type}` is a more specific mediatype.

Like text and HTML, you likely don't want to do this manually every time:

```php
$json = json_encode(
  $data,
  JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES
);
$stream = new Stream('php://temp', 'wb+');
$stream->write($json);
$response = new Response(
    $stream,
    StatusCodeInterface::STATUS_OK,
    ['Content-Type' => 'application/json']
);
```

To simplify this, we provide `Zend\Diactoros\Response\JsonResponse`, with the
following constructor signature:

```php
public function __construct(
    $data,
    $status = 200,
    array $headers = [],
    $encodingOptions = self::DEFAULT_JSON_FLAGS
) {
```

where `$encodingOptions` defaults to the flags specified in the previous
example.

This means our most common use case now becomes this:

```php
return new JsonResponse($data);
```

What if we want to return a JSON-formatted Problem Details response?

```php
return new JsonResponse(
    $details,
    StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
    ['Content-Type' => 'application/problem+json']
);
```

One common workflow we've seen with JSON responses is that developers often want
to manipulate them on the way out through middleware. As an example, they may
want to add additional `_links` elements to HAL responses, or add counts for
collections.

Starting in version 1.5.0, we provide a few extra methods on this particular
response type:

```php
public function getPayload() : mixed;
public function getEncodingOptions() : int;
public function withPayload(mixed $data) : JsonResponse;
public function withEncodingOptions(int $options) : JsonResponse;
```

Essentially, what happens is we now store not only the encoded `$data`
internally, but the raw data; this allows you to pull it, manipulate it, and
then create a new instance with the updated data. Additionally, we allow
specifying a different set of encoding options later; this can be useful, for
instance, for adding the `JSON_PRETTY_PRINT` flag when in development. When the
options are changed, the new instance will also re-encode the existing data.

First, let's look at altering the payload on the way out. zend-expressive-hal
injects `_total_items`, `_page`, and `_page_count` properties, and you may want
to remove the underscore prefix for each of these:

```php
function (ServerRequestInterface $request, DelegateInterface $delegate) : ResponseInterface
{
    $response = $delegate->process($request);
    if (! $response instanceof JsonResponse) {
        return $response;
    }

    $payload = $response->getPayload();
    if (! isset($payload['_total_items'])) {
        return $response;
    }

    $payload['total_items'] = $payload['_total_items'];
    unset($payload['_total_items']);

    if (isset($payload['_page'])) {
        $payload['page'] = $payload['_page'];
        $payload['page_count'] = $payload['_page_count'];
        unset($payload['_page'], $payload['_page_count']);
    }

    return $response->withPayload($payload);
}
```

Now, let's write middleware that sets the `JSON_PRETTY_PRINT` option when in
development mode:

```php
function (
    ServerRequestInterface $request,
    DelegateInterface $delegate
) : ResponseInterface use ($isDevelopmentMode) {
    $response = $delegate->process($request);

    if (! $isDevelopmentMode || ! $response instanceof JsonResponse) {
        return $response;
    }

    $options = $response->getEncodingOptions();
    return $response->withEncodingOptions($options | JSON_PRETTY_PRINT);
}
```

These features can be really powerful when shaping your API!

## Summary

The goal of PSR-7 is to provide the ability to standardize on interfaces for
your HTTP interactions. However, at some point you need to choose an actual
implementation, and your choice will often be shaped by the _features_ offered,
particularly if they provide convenience in your development process. Our goal
with these various custom response implementations is to provide _convenience_
to developers, allowing them to focus on what they need to return, not _how_ to
return it.

You can check out more in the [Diactoros documentation](https://docs.zendframework.com/zend-diactoros).
