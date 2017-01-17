---
layout: post
title: Implement an XML-RPC server with zend-xmlrpc
date: 2017-01-17T11:15:00-06:00
author: Matthew Weier O'Phinney
url_author: https://mwop.net/
permalink: /blog/2017-01-17-zend-xmlrpc-server.html
categories:
- blog
- components
- zend-xmlrpc

---

[zend-xmlrpc](https://docs.zendframework.com/zend-xmlrpc/) provides a
full-featured [XML-RPC](http://xmlrpc.scripting.com/spec.html) client and server
implementation. XML-RPC is a Remote Procedure Call protocol using HTTP as the
transport and XML for encoding the requests and responses.

> ### Author's note
>
> When I started at Zend in 2005, I was in their nascent eBiz division, tasked
> with maintaining and improving the web properties. Zend Framework was an
> internal/invite-only project, but we were asked to "dogfood" it for the
> website.
>
> Unfortunately, our web servers were still running PHP 4, and ZF was targeting
> PHP 5! Mike Naberezny, project lead of ZF at that time, suggested we set
> up a web services platform that our front-end would communicate with, and
> pointed me to the XML-RPC specification and PEAR's XML-RPC client.
> 
> That left me with an open problem: we still needed an XML-RPC server.
> 
> Fortunately, another developer had already contributed an XML-RPC client for
> the framework, which gave a basis for the value handling. I quickly wrote an
> XML-RPC server using that same value handling, and using reflection for method
> discovery. Mike accepted it into the, then, `Zend_XmlRpc` component. It became
> my first contribution to the framework. It has served as the basis for each of
> the server components, including the [zend-json-server I covered last week](/blog/2017-01-10-zend-json-server.html).

Each XML-RPC request consists of a method call, which names the procedure
(`methodName`) to call, along with its parameters. The server then returns a
response, the value returned by the procedure.

As an example of a request:

```http
POST /xml-rpc HTTP/1.1
Host: api.example.com
Content-Type: text/xml

<?xml version="1.0"?>
<methodCall>
	<methodName>add</methodName>
	<params>
		<param>
			<value><i4>20</i4></value>
		</param>
		<param>
			<value><i4>22</i4></value>
		</param>
	</params>
</methodCall>
```

The above is essentially requesting `add(20, 22)` from the server.

A response might look like this:

```http
HTTP/1.1 200 OK
Connection: close
Content-Type: text/xml

<?xml version="1.0"?>
<methodResponse>
	<params>
		<param>
			<value><i4>42</i4></value>
		</param>
	</params>
</methodResponse>
```

In the case of an error, you get a _fault_ response, detailing the problem:

```http
HTTP/1.1 200 OK
Connection: close
Content-Type: text/xml

<?xml version="1.0"?>
<methodResponse>
    <fault>
        <value>
            <struct>
                <member>
                    <name>faultCode</name>
                    <value><int>4</int></value>
                </member>
                <member>
                    <name>faultString</name>
                    <value><string>Too few parameters.</string></value>
                </member>
            </struct>
        </value>
    </fault>
</methodResponse>
```

> ### Content-Length
>
> The specification indicates that the `Content-Length` header must be present
> in both requests and responses, and must be correct. I have yet to work with any
> XML-RPC clients or servers that followed this restriction.

## Values

XML-RPC is meant to be intentionally simple, and support simple procedural
operations with a limited set of allowed values. It predates JSON, but
similarly defines a restricted list of allowed value types in order to allow
representing almost any data structure &mdash; and note that term, _data structure_.
Typed **objects** with _behavior_ are never transferred, only data. (This is how
SOAP differentiates from XML-RPC.)

Knowing what value types may be transmitted over XML-RPC allows you to determine
whether or not it's a good fit for your web service platform.

The values allowed include:

- Integers, via either `<int>` or `<i4>` tags. (`<i4>` points to the fact that
  the specification restricts integers to four-byte signed integers.)
- Booleans, via `<boolean>`; the values are either `0` or `1`.
- Strings, via `<string>`.
- Floats or doubles, via `<double>`.
- Date/Time values, in ISO-8601 format, via `<dateTime.iso8601>`.
- Base64-encoded binary values, via `<base64>`.

There are also two composite value types, `<struct>` and `<array>`. A `<struct>`
contains `<member>` values, which in turn contain a `<name>` and a `<value>`:

```xml
<struct>
    <member>
        <name>minimum</name>
        <value><int>0</int></value>
    </member>
    <member>
        <name>maximum</name>
        <value><int>100</int></value>
    </member>
</struct>
```

These can be visualized as _associative arrays_ in PHP.

An `<array>` consists of a `<data>` element containing any number of `<value>`
items:

```xml
<array>
    <data>
        <value><int>0</int></value>
        <value><int>10</int></value>
        <value><int>20</int></value>
        <value><int>30</int></value>
        <value><int>50</int></value>
    </data>
</array>
```

The values within an array or a struct do not need to be of the same type, which
makes them very suitable for translating to PHP structures.

While these values are easy enough to create and parse, doing so manually leads
to a lot of overhead, particularly if you want to ensure that your server and/or
client is robust. zend-xmlrpc provides all the tools to work with this 

## Automatically serving class methods

To simplify creating servers, zend-xmlrpc uses PHP's [Reflection API](http://php.net/Reflection)
to scan functions and class methods in order to expose them as XML-RPC services.
This allows you to add an arbitrary number of methods to your XML-RPC server,
which can them be handled via a single endpoint.

In vanilla PHP, this then looks like:

```php
$server = new Zend\XmlRpc\Server;
$server->setClass('Calculator');
echo $server->handle();
```

Internally, zend-xmlrpc will take care of type conversions from the incoming
request. To do so, however, you may need to document your types using slightly
different notation within your docblocks. As examples, the following types do
not have direct analogues in PHP:

- dateTime.iso8601
- base64
- struct

If you want to accept or return any of these types, document them:

```php
/**
 * @param dateTime.iso8601 $data
 * @param base64 $data
 * @param struct $map
 * @return base64
 */
function methodWithOddParameters($date, $data, array $map)
{
}
```

> ### Structs
>
> zend-xmlrpc _does_ contain logic to determine if an array value is an indexed
> array or an associative array, and will generally properly convert these.
> However, we still recommend documenting the more specific types as noted above
> for purposes of using the `system.methodHelp` functionality, which is detailed
> below.

You may also add functions:

```php
$server->addFunction('add');
```

A server can accept multiple functions and classes. However, be aware that when
doing so, you need to be careful about _naming conflicts_. Fortunately,
zend-xmlrpc has ways to resolve those, as well!

If you look at many XML-RPC examples, they will use method names such as
`calculator.add` or `transaction.process`. zend-xmlrpc, when performing
reflection, uses the method or function name by default, which will be the
portion following the `.` in the previous examples. However, you can also
_namespace_ these, using an additional argument to either `addFunction()` or
`setClass()`:

```php
// Exposes Calculator methods under calculator.*:
$server->setClass('Calculator', 'calculator');  

// Exposes transaction.process:
$server->addFunction('process', 'transaction');
```

This can be particularly useful when exposing multiple classes that may expose
the same method names.

## Server introspection

While not an official part of the standard, many servers and clients support the
[XML-RPC Introspection protocol](http://xmlrpc-c.sourceforge.net/introspection.html).
The protocol defines three methods:

- `system.listMethods`, which returns a struct of methods supported by the server.
- `system.methodSignature`, which returns a struct detailing the arguments to
  the requested method.
- `system.methodHelp`, which returns a string description of the requested method.

The server implementation in zend-xmlrpc supports these out-of-the-box, allowing
your clients to get information on exposed services!

> ### zend-xmlrpc client and introspection
>
> The client exposed within zend-xmlrpc will natively use the introspection
> protocol in order to provide a fluent, method-like way of invoking XML-RPC
> methods:
>
> ```php
> $client = new Zend\XmlRpc\Client('https://xmlrpc.example.com/');
> $service = $client->getProxy();             // invokes introspection!
> $value = $service->calculator->add(20, 22); // invokes calculator.add(20, 22)
> ```

## Faults and exceptions

By default, zend-xmlrpc catches exceptions in your service classes, and raises
fault responses. However, these fault responses omit the exception details by
default, to prevent leaking sensitive information.

You can, however, whitelist exception types with the server:

```php
use App\Exception;
use Zend\XmlRpc\Server\Fault;

Fault::attachFaultException(Exception\InvalidArgumentException::class);
```

When you do so, the exception code and message will be used to generate the
fault response. Note: any exception in that particular inheritance hierarchy
will then be exposed as well!

## Integrating with zend-mvc

The above examples all demonstrate usage in standalone scripts; what if you want
to use the server inside zend-mvc?

To do so, we need to do two things differently:

- We need to create our own `Zend\XmlRpc\Request` and seed it from the MVC
  request content.
- We need to cast the response returned by `Zend\XmlRpc\Server::handle()` to an
  MVC response.

```php
namespace Acme\Controller;

use Acme\Model\Calculator;
use Zend\XmlRpc\Request as XmlRpcRequest;
use Zend\XmlRpc\Response as XmlRpcResponse;
use Zend\XmlRpc\Server as XmlRpcServer;
use Zend\Mvc\Controller\AbstractActionController;

class XmlRpcController extends AbstractActionController
{
    private $calculator;

    public function __construct(Calculator $calculator)
    {
        $this->calculator = $calculator;
    }

    public function endpointAction()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        // Seed the XML-RPC request
        $xmlRpcRequest = new XmlRpcRequest();
        $xmlRpcRequest->loadXml($request->getContent());

        // Create the server
        $server = new XmlRpcServer();
        $server->setClass($this->calculator, 'calculator');

        /** @var XmlRpcResponse $xmlRpcResponse */
        $xmlRpcResponse = $server->handle($xmlRpcRequest);

        /** @var \Zend\Http\Response $response */
        $response = $this->getResponse();

        // Set the headers and content
        $response->getHeaders()->addHeaderLine('Content-Type', 'text/xml');
        $response->setContent($xmlRpcResponse->saveXml());
        return $response;
    }
}
```

> ### Inject your dependencies!
>
> You'll note that the above example accepts the `Acme\Model\Calculator` instance
> via its constructor. This means that you will need to provide a factory for
> your controller, to ensure that it is injected with a fully configured
> instance &mdash; and that likely also means a factory for the model, too.
>
> To simplify this, you may want to check out the [ConfigAbstractFactory](https://docs.zendframework.com/zend-servicemanager/config-abstract-factory/)
> or [ReflectionBasedAbstractFactory](https://docs.zendframework.com/zend-servicemanager/reflection-abstract-factory/),
> both of which were introduced in version 3.2.0 of zend-servicemanager.

## Using zend-xmlrpc's server within PSR-7 middleware

Using the zend-xmlrpc server within PSR-7 middleware is similar to zend-mvc.

```php
namespace Acme\Controller;

use Acme\Model\Calculator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\XmlRpc\Request as XmlRpcRequest;
use Zend\XmlRpc\Response as XmlRpcResponse;
use Zend\XmlRpc\Server as XmlRpcServer;

class XmlRpcMiddleware
{
    private $calculator;

    public function __construct(Calculator $calculator)
    {
        $this->calculator = $calculator;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) {
        // Seed the XML-RPC request
        $xmlRpcRequest = new XmlRpcRequest();
        $xmlRpcRequest->loadXml((string) $request->getBody());

        $server = new XmlRpcServer();
        $server->setClass($this->calculator, 'calculator');

        /** @var XmlRpcResponse $xmlRpcResponse */
        $xmlRpcResponse = $server->handle($xmlRpcRequest);

        return new HtmlResponse(
            $xmlRpcResponse->saveXml(),
            200,
            ['Content-Type' => 'text/xml']
        );
    }
}
```

In the above example, I use the [zend-diactoros](https://docs.zendframework.com/zend-diactoros)-specific
`HtmlResponse` type to generate the response; this could be any other response
type, as long as the `Content-Type` header is set correctly, and the status code
is set to 200.

Per the [note above](#inject-your-dependencies), you will need to
configure your dependency injection container to inject the middleware instance
with the model.

## Summary

While XML-RPC may not be _du jour_, it is a tried and true method of exposing
web services that has persisted for close to two decades.  zend-xmlrpc's server
implementation provides a flexible, robust, and simple way to create XML-RPC
services around the classes and functions you define in PHP, making it possible
to use it standalone, or within _any_ application framework you might be using.
Hopefully the examples above will aid you in adapting it for use within your own
application!

Visit the [zend-xmlrpc server documentation](https://docs.zendframework.com/zend-xmlrpc/server/)
to find out what else you might be able to do with this component!
