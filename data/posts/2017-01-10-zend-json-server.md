---
layout: post
title: Implement JSON-RPC with zend-json-server
date: 2017-01-10T11:45:00-06:00
author: Matthew Weier O'Phinney
url_author: https://mwop.net/
permalink: /blog/2017-01-10-zend-json-server.html
categories:
- blog
- components
- zend-json-server

---

[zend-json-server](https://docs.zendframework.com/zend-json-server/) provides a
[JSON-RPC](http://groups.google.com/group/json-rpc/) implementation. JSON-RPC is
similar to XML-RPC or SOAP in that it implements a Remote Procedure Call server
at a single URI using a predictable calling semantic. Like each of these other
protocols, it provides the ability to introspect the server in order to
determine what calls are available, what arguments each call expects, and the
expected return value(s); JSON-RPC implements this via a 
[Service Mapping Description (SMD)](http://www.jsonrpc.org/specification), which
is usually available via an HTTP `GET` request to the server.

zend-json-server was designed to work standalone, allowing you to map a URL to a
specific script that then handles the request:

```php
$server = new Zend\Json\Server\Server();
$server->setClass('Calculator');

// SMD request
if ('GET' === $_SERVER['REQUEST_METHOD']) {
    // Indicate the URL endpoint, and the JSON-RPC version used:
    $server->setTarget('/json-rpc')
           ->setEnvelope(Zend\Json\Server\Smd::ENV_JSONRPC_2);

    // Grab the SMD
    $smd = $server->getServiceMap();

    // Return the SMD to the client
    header('Content-Type: application/json');
    echo $smd;
    return;
}

// Normal request
$server->handle();
```

What the above example does is:

- Create a server.
- Attach a class or object to the server. The server introspects that class in
  order to expose any public methods on it as calls on the server itself.
- If an HTTP `GET` request occurs, we present the service mapping description.
- Otherwise, we attempt to handle the request.

All server components in Zend Framework work similar to the above. Introspection
via function or class reflection allows quickly creating and exposing services
via these servers, as well as enables the servers to provide SMD, WSDL, or
XML-RPC system information.

However, this approach can lead to difficulties:

- What if I need access to other application services? or want to use the
  fully-configured application dependency injection container?
- What if I want to be able to control the URI via a router?
- What if I want to be able to add authentication or authorization in front of
  the server?

In other words, **_how do I use the JSON-RPC server as part of a larger application?_**

Below, I'll outline using zend-json-server in both a Zend Framework MVC
application, as well as via PSR-7 middleware. In both cases, you may assume that
`Acme\ServiceModel` is a class exposing public methods we wish to expose via the
server.

## Using zend-json-server within zend-mvc

To use zend-json-server within a zend-mvc application, you will need to:

- Provide a `Zend\Json\Server\Response` instance to the `Server` instance.
- Tell the `Server` instance to return the response.
- Populate the MVC's response from the `Server`'s response.
- Return the MVC response (which will short-circuit the view layer).

This third step requires a bit of logic, as the default response type,
`Zend\Json\Server\Response\Http`, does some logic around setting headers that
you'll need to duplicate.

A full example will look like the following:

```php
namespace Acme\Controller;

use Acme\ServiceModel;
use Zend\Json\Server\Response as JsonResponse;
use Zend\Json\Server\Server as JsonServer;
use Zend\Mvc\Controller\AbstractActionController;

class JsonRpcController extends AbstractActionController
{
    private $model;

    public function __construct(ServiceModel $model)
    {
        $this->model = $model;
    }

    public function endpointAction()
    {
        $server = new JsonServer();
        $server
            ->setClass($this->model)
            ->setResponse(new JsonResponse())
            ->setReturnResponse();

        /** @var JsonResponse $jsonRpcResponse */
        $jsonRpcResponse = $server->handle();

        /** @var \Zend\Http\Response $response */
        $response = $this->getResponse();

        // Do we have an empty response?
        if (! $jsonRpcResponse->isError()
            && null === $jsonRpcResponse->getId()
        ) {
            $response->setStatusCode(204);
            return $response;
        }

        // Set the content-type
        $contentType = 'application/json-rpc';
        if (null !== ($smd = $jsonRpcResponse->getServiceMap())) {
            // SMD is being returned; use alternate content type, if present
            $contentType = $smd->getContentType() ?: $contentType;
        }

        // Set the headers and content
        $response->getHeaders()->addHeaderLine('Content-Type', $contentType);
        $response->setContent($jsonRpcResponse->toJson());
        return $response;
    }
}
```

> ### Inject your dependencies!
>
> You'll note that the above example accepts the `Acme\ServiceModel` instance
> via its constructor. This means that you will need to provide a factory for
> your controller, to ensure that it is injected with a fully configured
> instance &mdash; and that likely also means a factory for the model, too.
>
> To simplify this, you may want to check out the [ConfigAbstractFactory](https://docs.zendframework.com/zend-servicemanager/config-abstract-factory/)
> or [ReflectionBasedAbstractFactory](https://docs.zendframework.com/zend-servicemanager/reflection-abstract-factory/),
> both of which were introduced in version 3.2.0 of zend-servicemanager.

## Using zend-json-server within PSR-7 middleware

Using zend-json-server within PSR-7 middleware is similar to zend-mvc:

- Provide a `Zend\Json\Server\Response` instance to the `Server` instance.
- Tell the `Server` instance to return the response.
- Create and return a PSR-7 response based on the `Server`'s response.

The code ends up looking like the following:

```php
namespace Acme\Controller;

use Acme\ServiceModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\TextResponse;
use Zend\Json\Server\Response as JsonResponse;
use Zend\Json\Server\Server as JsonServer;

class JsonRpcMiddleware
{
    private $model;

    public function __construct(ServiceModel $model)
    {
        $this->model = $model;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) {
        $server = new JsonServer();
        $server
            ->setClass($this->model)
            ->setResponse(new JsonResponse())
            ->setReturnResponse();

        /** @var JsonResponse $jsonRpcResponse */
        $jsonRpcResponse = $server->handle();

        // Do we have an empty response?
        if (! $jsonRpcResponse->isError()
            && null === $jsonRpcResponse->getId()
        ) {
            return new EmptyResponse();
        }


        // Get the content-type
        $contentType = 'application/json-rpc';
        if (null !== ($smd = $jsonRpcResponse->getServiceMap())) {
            // SMD is being returned; use alternate content type, if present
            $contentType = $smd->getContentType() ?: $contentType;
        }

        return new TextResponse(
            $jsonRpcResponse->toJson(),
            200,
            ['Content-Type' => $contentType]
        );
    }
}
```

In the above example, I use a couple of [zend-diactoros](https://docs.zendframework.com/zend-diactoros)-specific
response types to ensure that we have no extraneous information in the returned
responses. I use `TextResponse` specifically, as the `toJson()` method on the
zend-json-server response returns the actual JSON string, versus a data
structure that can be cast to JSON.

Per the [note above](#inject-your-dependencies), you will need to
configure your dependency injection container to inject the middleware instance
with the model.

## Summary

zend-json-server provides a flexible, robust, and simple way to create JSON-RPC
services. The design of the component makes it possible to use it standalone, or
within _any_ application framework you might be using. Hopefully the examples
above will aid you in adapting it for use within your own application!

Visit the [zend-json-server documentation](https://docs.zendframework.com/zend-json-server/)
to find out what else you might be able to do with this component!
