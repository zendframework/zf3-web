---
layout: post
title: Emitting Responses with Diactoros
date: 2017-09-14T11:02:00-05:00
author: Matthew Weier O'Phinney
url_author: https://mwop.net/
permalink: /blog/2017-09-14-diactoros-emitters.html
categories:
- blog
- psr7
- diactoros
- api

---

When writing middleware-based applications, at some point you will need to _emit
your response_.

[PSR-7](http://www.php-fig.org/psr/psr-7/) defines the various interfaces
related to HTTP messages, but does not define how they will be used.
[Diactoros](https://docs.zendframework.org/zend-diactoros/) defines several
utility classes for these purposes, including a `ServerRequestFactory` for
generating a `ServerRequest` instance from the PHP SAPI in use, and a set of
_emitters_, for emitting responses back to the client. In this post, we'll
detail the purpose of emitters, the emitters shipped with Diactoros, and some
strategies for emitting content to your users.

## What is an emitter?

In vanilla PHP applications, you might call one or more of the following
functions in order to provide a response to your client:

- `http_response_code()` for emitting the HTTP response code to use; this must
  be called before any output is emitted.
- `header()` for emitting response headers. Like `http_response_code()`, this
  must be called before any output is emitted. It may be called multiple times,
  in order to set multiple headers.
- `echo()`, `printf()`, `var_dump()`, and `var_export()` will each emit output
  to the current output buffer, or, if none is present, directly to the client.

One aspect PSR-7 aims to resolve is the ability to generate a response
piece-meal, including adding content and headers in whatever order your
application requires. To accomplish this, it provides a `ResponseInterface` with
which your application interacts, and which aggregates the response status code,
its headers, and all content.

Once you have a complete response, however, you need to emit it.

Diactoros provides _emitters_ to solve this problem. Emitters all implement
`Zend\Diactoros\Response\EmitterInterface`:

```php
namespace Zend\Diactoros\Response;

use Psr\Http\Message\ResponseInterface;

interface EmitterInterface
{
    /**
     * Emit a response.
     *
     * Emits a response, including status line, headers, and the message body,
     * according to the environment.
     *
     * Implementations of this method may be written in such a way as to have
     * side effects, such as usage of header() or pushing output to the
     * output buffer.
     *
     * Implementations MAY raise exceptions if they are unable to emit the
     * response; e.g., if headers have already been sent.
     *
     * @param ResponseInterface $response
     */
    public function emit(ResponseInterface $response);
}
```

Diactoros provides two emitter implementations, both geared towards standard PHP
SAPI implementations:

- `Zend\Diactoros\Emitter\SapiEmitter`
- `Zend\Diactoros\Emitter\SapiStreamEmitter`

Internally, they operate very similarly: they emit the response status code, all
headers, and the response body content. Prior to doing so, however, they check
for the following conditions:

- Headers have not yet been sent.
- If any output buffers exist, no content is present.

If either of these conditions is not true, the emitters raise an exception.
This is done to ensure that consistent content can be emitted; mixing PSR-7 and
global output leads to unexpected and inconsistent results. If you are using
middleware, use things like the error log, loggers, etc. if you want to debug,
instead of mixing strategies.

## Emitting files

As noted above, one of the two emitters is the `SapiStreamEmitter`. The normal
`SapiEmitter` emits the response body at once via a single `echo` statement.
This works for most general markup and JSON payloads, but when returning files
(for example, when providing file downloads via your application), this strategy
can quickly exhaust the amount of memory PHP is allowed to consume.

The `SapiStreamEmitter` is designed to answer the problem of file downloads. It
emits a chunk at a time (8192 bytes by default).  While this can mean a bit more
performance overhead when emitting a large file, as you'll have more method
calls, it also leads to reduced _memory_ overhead, as less content is in memory
at any given time.

The `SapiStreamEmitter` has another important feature, however: it allows
sending _content ranges_.

Clients can opt-in to receiving small chunks of a file at a time. While this
means more network calls, it can also help prevent corruption of large files by
allowing the client to re-try failed requests in order to stitch together the
full file. Doing so also allows providing progress status, or even buffering
streaming content.

When requesting content ranges, the client will pass a `Range` header:

```http
Range: bytes=1024-2047
```

It is up to the server then to detect such a header and return the requested
range. Servers indicate that they are doing so by responding with a `Content-Range`
header with the range of bytes being returned and the total number of bytes
possible; the response body then only contains those bytes.

```php
Content-Range: bytes=1024-2047/11576
```

As an example, middleware that allows returning a content range might look like
the following:

```php
function (ServerRequestInterface $request, DelegateInterface $delegate) : ResponseInterface
{
    $stream = new Stream('path/to/download/file', 'r');
    $response = new Response($stream);

    $range = $request->getHeaderLine('range');
    if (empty($range)) {
        return $response;
    }

    $size  = $body->getSize();
    $range = str_replace('=', ' ', $range);
    $range .= '/' . $size;

    return $response->withHeader('Content-Range', $range);
}
```

> You'll likely want to validate that the range is within the size of the file, too!

The above code emits a `Content-Range` response header if a `Range` header is in
the request. However, how do we ensure _only_ that range of bytes is emitted?

By using the `SapiStreamEmitter`! This emitter will detect the `Content-Range`
header and use it to read and emit only the bytes specified by that header; no
extra work is necessary!

## Mixing and matching emitters

The `SapiEmitter` is perfect for content generated within your application
&mdash; HTML, JSON, XML, etc. &mdash; as such content is usually of reasonable
length, and will not exceed normal memory and resource limits.

The `SapiStreamEmitter` is ideal for returning file downloads, but can lead to
performance overhead when emitting standard application content.

How can you mix and match the two?

Expressive answers this question by providing
`Zend\Expressive\Emitter\EmitterStack`. The class acts as a stack (last in,
first out), executing each emitter composed until one indicates it has handled
the response.

This class capitalizes on the fact that the return value of `EmitterInterface`
is undefined. Emitters that return a boolean `false` indicate they were _unable
to handle the response_, allowing the `EmitterStack` to move to the next emitter
in the stack. The first emitter to return a non-`false` value halts execution.

Both the emitters defined in zend-diactoros return `null` by default. So, if we
want to create a stack that first tries `SapiStreamEmitter`, and then defaults
to `SapiEmitter`, we could do the following:

```php
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\EmitterInterface;
use Zend\Diactoros\Response\SapiEmitter;
use Zend\Diactoros\Response\SapiStreamEmitter;
use Zend\Expressive\Emitter\EmitterStack;

$emitterStack = new EmitterStack();
$emitterStack->push(new SapiEmitter());
$emitterStack->push(new class implements EmitterInterface {
    public function emit(ResponseInterface $response)
    {
        $contentSize = $response->getBody()->getSize();

        if ('' === $response->getHeaderLine('content-range')
            && $contentSize < 8192
        ) {
            return false;
        }

        $emitter = new SapiStreamEmitter();
        return $emitter->emit($response);
    }
});
```

The above will execute our anonymous class as the first emitter. If the response
has a `Content-Range` header, or if the size of the content is greater than 8k,
it will use the `SapiStreamEmitter`; otherwise, it returns `false`, allowing the
next emitter in the stack, `SapiEmitter`, to execute. Since that emitter always
returns null, it acts as a default emitter implementation.

In Expressive, if you were to wrap the above in a factory that returns the
`$emitterStack`, and assign that factory to the
`Zend\Diactoros\Emitter\EmitterInterface` service, then the above stack will be
used by `Zend\Expressive\Application` for the purpose of emitting the
application response!

## Summary

Emitters provide you the ability to return the response you have aggregated in
your application to the client. They are intended to have side-effects: sending
the response code, response headers, and body content. Different emitters can
use different strategies when emitting responses, from simply `echo`ing content,
to iterating through chunks of content (as the `SapiStreamEmitter` does). Using
Expressive's `EmitterStack` can provide you with a way to select different
emitters for specific response criteria.

For more information:

- Read the [Diactoros emitter documentation](https://docs.zendframework.com/zend-diactoros/emitting-responses/)
- Read the [Expressive emitter documentation](https://docs.zendframework.com/zend-expressive/features/emitters/)
