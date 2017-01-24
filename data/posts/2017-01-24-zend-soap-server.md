---
layout: post
title: Implement a SOAP server with zend-soap
date: 2017-01-24T11:15:00-06:00
author: Matthew Weier O'Phinney
url_author: https://mwop.net/
permalink: /blog/2017-01-24-zend-soap-server.html
categories:
- blog
- components
- zend-soap

---

[zend-soap](https://docs.zendframework.com/zend-soap/) provides a full-featured
[SOAP](https://en.wikipedia.org/wiki/SOAP) implementation. SOAP is an XML-based
web protocol designed to allow describing _messages_, and, optionally,
operations to perform.  It's similar to [XML-RPC](/blog/2017-01-17-zend-xmlrpc-server.html),
but with a few key differences:

- Arbitrary data structures may be described; you are not limited to the basic
  scalar, list, and struct types of XML-RPC. Messages are often serializations
  of specific object types on either or both the client and server. The
  SOAP message may include information on its own structure to allow the server
  or client to determine how to interpret the message.

- Multiple _operations_ may be described in a message as well, versus the one
  call, one operation structure of XML-RPC.

In other words, it's an _extensible_ protocol. This provides obvious benefits,
but also a disadvantage: creating and parsing SOAP messages can quickly become
quite complex!

To alleviate that complexity, Zend Framework provides the zend-soap component,
which includes a server implementation.

> ### Why these articles on RPC services?
>
> In the past couple weeks, we've covered [JSON-RPC](/blog/2017-01-10-zend-json-server.html)
> and [XML-RPC](/blog/2017-01-17-zend-xmlrpc-server.html). One feedback we've
> seen is: why bother &mdash; shouldn't we be creating REST services instead?
>
> We love REST; one of our projects is [Apigility](https://apigility.org), which
> allows you to simply and quickly build REST APIs. However, there are occasions
> where RPC may be a better fit:
>
> - If your services are less _resource_ oriented, and more _function_ oriented
>   (e.g., providing calculations).
>
> - If consumers of your services may need more uniformity in the service
>   architecture in order to ensure they can quickly and easily consume the
>   services, without needing to create unique tooling for each service exposed.
>   While the goal of REST is to offer discovery, when every payload to send or
>   receive is different, this can often lead to an explosion of code when
>   consuming many services.
>
> - Some organizations and companies may standardize on certain web service
>   protocols due to existing tooling, ability to train developers, etc.
>
> While REST may be the preferred way to architect web services, these and
> other reasons often dictate other approaches. As such, we provide these RPC
> alternatives for PHP developers.

## What benefits does it offer over the PHP extension?

PHP provides SOAP client and server capabilities already via its
[SOAP extension](http://php.net/soap); why do we offer a component?

By default, PHP's `SoapServer::handle()` will:

- Grab the POST body (`php://input`), unless an XML string is passed to it.
- Emit the headers and SOAP XML response body to the output buffer.

Exceptions or PHP errors raised during processing _may_ result in a SOAP fault
response, with no details, or can result in invalid/empty SOAP responses
returned to the client.

The primary benefit zend-soap provides, then, is _error handling_. You can
whitelist exception types, and, when encountered, fault responses containing the
exception details will be returned. PHP errors will be emitted as SOAP faults.

The next thing that zend-soap offers is WSDL generation. WSDL allows you to
_describe_ the web services you offer, so that clients know how to work with
your services. ext/soap provides no functionality around creating WSDL; it
simply expects that you will have a valid one for use with the client or server.

zend-soap provides an `AutoDiscover` class that uses reflection on the classes
and functions you pass it in order to build a valid WSDL for you; you can then
provide this to your server and your clients.

## Creating a server

There are two parts to providing a SOAP server:

- Providing the server itself, which will handle requests.
- Providing the WSDL.

Building each follows the same process; you simply emit them with different HTTP
`Content-Type` headers, and under different HTTP methods (the server will always
react to POST requests, while WSDL should be available via GET).

First, let's define a function for populating a server instance with classes
and functions:

```php
use Acme\Model;

function populateServer($server, array $env)
{
    // Expose a class and its methods:
    $server->setClass(Model\Calculator::class);

    // Expose an object instance and its methods:
    $server->setObject(new Model\Env($env));

    // Expose a function:
    $server->addFunction('Acme\Model\ping');
}
```

Note that `$server` is not type-hinted; that will become more obvious soon.

Now, let's assume that the above function is available to us, and use it to
create our WSDL:

```php
// File /soap/wsdl.php

use Zend\Soap\AutoDiscover;

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    // Only handle GET requests
    header('HTTP/1.1 400 Client Error');
    exit;
}

$wsdl = new AutoDiscover();
populateServer($wsdl, $_ENV);
$wsdl->handle();
```

Done! The above will emit the WSDL for either the client or server to consume.

Now, let's create the server. The server requires a few things:

- The public, HTTP-accessible location of the WSDL.
- `SoapServer` options, including the `actor` URI for the server and SOAP
  version targeted.

Additionally, we'll need to notify the server of its capabilities, via the
`populateServer()` function.

```php
// File /soap/server.php

use Zend\Soap\Server;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Only handle POST requests
    header('HTTP/1.1 400 Client Error');
    exit;
}

$server = new Server(dirname($_SERVER['REQUEST_URI']) . '/wsdl.php', [
    'actor' => $_SERVER['REQUEST_URI'],
]);

populateServer($server, $_ENV);
$server->handle();
```

The reason for the lack of type-hint should now be clear; both the `Server` and
`AutoDiscover` classes have the same API for populating the instances with
classes, objects, and functions; having a common function for doing so allows us
to ensure the WSDL and server do not go out of sync.

From here, you can point your clients at `/soap/server.php` on your domain, and
they will have all the information they need to work with your service.

## Using zend-soap within a zend-mvc application

The above details an approach using vanilla PHP; what about using zend-soap
within a zend-mvc context?

To do this, we'll need to learn a few more things.

First, you can provide `Server::handle()` with the _request_ to process. This
must be one of the following:

- a `DOMDocument`
- a `DOMNode`
- a `SimpleXMLElement`
- an object implementing `__toString()`, where that method returns an XML string
- an XML string

We can grab this information from the MVC request instance's body content.

Second, we will need the server to _return_ the response, so we can use it to
populate the MVC response instance. We can do that by calling
`Server::setReturnResponse(true)`. When we do, `Server::handle()` will return an
XML string representing the SOAP response message.

Let's put it all together:

```php
namespace Acme\Controller;

use Acme\Model;
use Zend\Soap\AutoDiscover as WsdlAutoDiscover;
use Zend\Soap\Server as SoapServer;
use Zend\Mvc\Controller\AbstractActionController;

class SoapController extends AbstractActionController
{
    private $env;

    public function __construct(Model\Env $env)
    {
        $this->env = $env;
    }

    public function wsdlAction()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        if (! $request->isGet()) {
            return $this->prepareClientErrorResponse('GET');
        }

        $wsdl = new WsdlAutoDiscover();
        $this->populateServer($wsdl);

        /** @var \Zend\Http\Response $response */
        $response = $this->getResponse();

        $response->getHeaders()->addHeaderLine('Content-Type', 'application/wsdl+xml');
        $response->setContent($wsdl->toXml());
        return $response;
    }

    public function serverAction()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        if (! $request->isPost()) {
            return $this->prepareClientErrorResponse('POST');
        }

        // Create the server
        $server = new SoapServer(
            $this->url()
                ->fromRoute('soap/wsdl', [], ['force_canonical' => true]),
            [
                'actor' => $this->url()
                    ->fromRoute('soap/server', [], ['force_canonical' => true]),
            ]
        );
        $server->setReturnResponse(true);
        $this->populateServer($server);

        $soapResponse = $server->handle($request->getContent());

        /** @var \Zend\Http\Response $response */
        $response = $this->getResponse();

        // Set the headers and content
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/soap+xml');
        $response->setContent($soapResponse);
        return $response;
    }

    private function prepareClientErrorResponse($allowed)
    {
        /** @var \Zend\Http\Response $response */
        $response = $this->getResponse();
        $response->setStatusCode(405);
        $response->getHeaders()->addHeaderLine('Allow', $allowed);
        return $response;
    }

    private function populateServer($server)
    {
        // Expose a class and its methods:
        $server->setClass(Model\Calculator::class);
    
        // Expose an object instance and its methods:
        $server->setObject($this->env);
    
        // Expose a function:
        $server->addFunction('Acme\Model\ping');
    }
}
```

The above assumes you've created routes `soap/server` and `soap/wsdl`, and uses
those to generate the URIs for the server and WSDL, respectively; the
`soap/server` route should map to the `SoapController::serverAction()` method
and the `soap/wsdl` route should map to the `SoapController::wsdlAction()`
method.

> ### Inject your dependencies!
>
> You'll note that the above example accepts the `Acme\Model\Env` instance via
> its constructor, allowing us to inject a fully-configured instance into the
> server and/or WSDL autodiscovery. This means that you will need to provide a
> factory for your controller, to ensure that it is injected with a fully
> configured instance &mdash; and that likely also means a factory for the
> model, too.
>
> To simplify this, you may want to check out the [ConfigAbstractFactory](https://docs.zendframework.com/zend-servicemanager/config-abstract-factory/)
> or [ReflectionBasedAbstractFactory](https://docs.zendframework.com/zend-servicemanager/reflection-abstract-factory/),
> both of which were introduced in version 3.2.0 of zend-servicemanager.

## Using zend-soap within PSR-7 middleware

Using zend-soap in PSR-7 middleware is essentially the same as what we detail
for zend-mvc: you'll need to pull the request content for the server, and use
the SOAP response returned to populate a PSR-7 response instance.

The example below assumes the following:

- You are using the [UrlHelper and ServerUrlHelper from zend-expressive-helpers](https://docs.zendframework.com/zend-expressive/features/helpers/url-helper)
  to generate URIs.
- You are routing to each middleware such that:
  - The 'soap.server' route will map to the `SoapServerMiddleware`, and only
    allow POST requests.
  - The 'soap.wsdl' route will map to the `WsdlMiddleware`, and only
    allow GET requests.

```php
namespace Acme\Middleware;

use Acme\Model;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\TextResponse;
use Zend\Soap\AutoDiscover as WsdlAutoDiscover;
use Zend\Soap\Server as SoapServer;

trait Common
{
    private $env;

    private $urlHelper;

    private $serverUrlHelper;

    public function __construct(
        Model\Env $env,
        UrlHelper $urlHelper,
        ServerUrlHelper $serverUrlHelper
    ) {
        $this->env = $env;
        $this->urlHelper = $urlHelper;
        $this->serverUrlHelper = $serverUrlHelper;
    }

    private function populateServer($server)
    {
        // Expose a class and its methods:
        $server->setClass(Model\Calculator::class);
    
        // Expose an object instance and its methods:
        $server->setObject($this->env);
    
        // Expose a function:
        $server->addFunction('Acme\Model\ping');
    }
}

class SoapServerMiddleware
{
    use Common;

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) {
        $server = new SoapServer($this->generateUri('soap.wsdl'), [
            'actor' => $this->generateUri('soap.server')
        ]);
        $server->setReturnResponse(true);
        $this->populateServer($server);

        $xml = $server->handle((string) $request->getBody());

        return new TextResponse($xml, 200, [
            'Content-Type' => 'application/soap+xml',
        ]);
    }

    private function generateUri($route)
    {
        return ($this->serverUrlHelper)(
            ($this->urlHelper)($route)
        );
    }
}

class WsdlMiddleware
{
    use Common;

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) {
        $server = new WsdlAutoDiscover();
        $this->populateServer($server);

        return new TextResponse($server->toXml(), 200, [
            'Content-Type' => 'application/wsdl+xml',
        ]);
    }
}
```

Since each middleware has the same basic construction, I've created a trait with
the common functionality, and composed it into each middleware. As you will
note, the actual work of each middleware is relatively simple; create a server,
and marshal a resposne to return.

In the above example, I use the [zend-diactoros](https://docs.zendframework.com/zend-diactoros)-specific
`TextResponse` type to generate the response; this could be any other response
type, as long as the `Content-Type` header is set correctly, and the status code
is set to 200.

Per the [note above](#inject-your-dependencies), you will need to
configure your dependency injection container to inject the middleware instances
with the model and helpers.

## Summary

While SOAP is often maligned in PHP circles, it is still in wide use within
enterprises, and used in many cases to provide cross-platform web services with
predictable behaviors. It can be quite complex, but zend-soap helps smooth out
the bulk of the complexity.  You can use it standalone, within a Zend Framework
MVC application, or within _any_ application framework you might be using.

Visit the [zend-soap documentation](https://docs.zendframework.com/zend-soap/)
to find out what else you might be able to do with this component!
