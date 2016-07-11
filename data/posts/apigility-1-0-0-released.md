---
layout: post
title: Apigility 1.0.0 Released!
date: 2014-05-07T17:00:00Z
update: 2014-05-07T17:00:00Z
author: Matthew Weier O'Phinney
url_author: http://mwop.net/
permalink: /blog/apigility-1-0-0-released.html
categories:
- blog
- apigility
- released

---

![Apigility](/images/ag-hero.png)We're happy to announce the immediate availability of Apigility 1.0.0!

- <http://apigility.org/download>

Apigility is the world's easiest way to create robust, well-formed, documented, and secure APIs.

We've noticed that web development has fundamentally shifted over the past few years. Server side applications that return heavy loads of HTML markup are waning; instead APIs are driving the web. APIs are being used to enable inter-division data exchange. APIs are being used as the building blocks for web applications -- allowing user interface experts the flexibility to change the look and feel of a website with no intervention from the server-side application developers. APIs are driving and empowering the mobile web, allowing creation of both native mobile applications and mobile web applications.

Apigility exists to allow _you_ to expose _your_ business logic as an API.

Opinionated
-----------

APIs are not just about tossing around some JSON or XML. They require a ton of architectural decisions every step of the way as you implement the API:

- How will you handle HTTP method negotiation?
- How will you handle content negotiation?
- How will you handle authentication?
- How will you handle authorization?
- How will you handle input validation?
- What representation format will you use?
- How will you represent errors?
- How will you document your API?

Most of these questions have no single, correct answer. Many standards, proposals, and drafts exist for all aspects of APIs. As a developer, you have to wade through them and choose which you will use, and how you will put them together.

In short, when developing an API, you often spend an equal or greater amount of time developing the architecture for the API as you would writing the code you want to expose in the first place.

Apigility is opinionated _for you_, and provides a flexible and robust engine that answers these questions:

- [application/hal+json](http://tools.ietf.org/html/draft-kelly-json-hal-06) is  
 used for the representation format (but you can add your own representations  
 if you really want).
- [application/problem+json](http://tools.ietf.org/html/draft-nottingham-http-problem-06)  
 is used for representing errors.
- HTTP method negotiation is handled early, returning appropriate response  
 status codes and headers when invalid methods are detected. Support for  
`OPTIONS` requests is built in.
- Content negotiation is handled early, returning appropriate response  
 status codes and headers when we cannot return an appropriate representation,  
 or cannot understand the data provided to the application.
- Authentication is handled early, returning appropriate response  
 status codes and headers when invalid credentials are detected. We provide  
 HTTP Basic, HTTP Digest, and OAuth2 support out-of-the-box, but provide a  
 flexible, event-driven system for implementing or integrating your own  
 authentication systems.
- Authorization is handled early, returning appropriate response  
 status codes and headers when access is not allowed. We provide an ACL-based  
 system that can be easily extended to provide user-specific permissions as  
 well as an event-driven system for implementing or integrating your own  
 authorization systems if needed.
- Input validation is handled early, returning appropriate response status codes  
 and error messages when invalid data is detected.
- Documentation is provided out of the box. We provide HTML, JSON, and  
[Swagger](https://helloreverb.com/developers/swagger) representations; we  
 anticipate adding more in the future. Documentation is generated automatically  
 from your API configuration, and you are able to add descriptions and more  
 detail as desired.

We make decisions so you don't have to. However, you'll note that we've created flexibility so that if _you_ have an opinion, you can integrate it!

Interface with your API
-----------------------

Apigility is not just an engine. Apigility also provides a web-based Admin UI to allow you to quickly create and modify your API and services, set up authentication, create authorization rules, set up validations for incoming data, and write document.

 ![Apigility Dashboard](/images/apigility-1.0.0-dashboard.png)The Admin UI is built using [AngularJS](https://angularjs.org/), and is backed by... an API! (We're taking the "API First" mantra very seriously!)

Get Started in Seconds
----------------------

You can install Apigility in seconds; execute the following command in your shell:

 
    <pre class="console">$ curl -sS http://apigility.org/install | php

or, if you don't have [curl](http://curl.haxx.se/) installed, use the PHP interpreter itself:

 
    $ php -r "readfile('http://apigility.org/install');" | php

Deploy Anywhere
---------------

Apigility is built on top of Zend Framework 2, and has tools to ensure that the Admin UI is only present in development mode. As such, you can deploy Apigility anywhere you normally deploy PHP applications: a local server, your web host provider, or the cloud.

We provide a tool, [ZFDeploy](https://github.com/zfcampus/zf-deploy), to aid in creating deployment packages, including Zend Server ZPK files.

Modular
-------

Apigility consists of more than a dozen Zend Framework 2 modules, each doing one specific task in the workflow. As such, you can mix and match these modules in your own ZF2 applications, or even [add Apigility to an existing application](http://apigility.org/documentation/recipes/apigility-in-an-existing-zf2-application).

The Future
----------

Reaching 1.0 is a huge milestone -- but it's not the end of the road!

We already have a number of great features waiting in the wings for a 1.1 release: Doctrine-Connected services, Database Autodiscovery, Mongo-Connected services, and HTTP Caching.

What will _you_ build today?
