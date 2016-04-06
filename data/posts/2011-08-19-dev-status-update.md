---
layout: post
title: 2011-08-19 Dev status update
date: 2011-08-19 12:32
update: 2011-08-19 12:32
author: Enrico Zimuel
url_author: http://www.zimuel.it/
permalink: /blog/2011-08-19-dev-status-update.html
categories:
- blog
- dev

---

 The first weekly status update about the development of ZF2.

Community Initiatives
---------------------

 Obviously, community interaction has exploded. Since last week, we've had almost 400 messages in the [zf-contributors mailing list](http://zend-framework-community.634137.n4.nabble.com/ZF-Contributor-f680267.html), an IRC meeting, and the start of a dedicated "ZF2" area of the main website (if you're reading this, you're in it).

Topics that have been under heavy discussion include:

- What will modules look like, and how will they be installed (and potentially distributed).
- Process: how should the proposal process work moving forward, and what ideas fit for it. One item we've agreed upon is that for architectural issues, posting to the mailing list, discussing in IRC, and creating RFC-style pages in the wiki is probably better.
- Visualizing development status: [a page has been built](http://framework.zend.com/zf2/status), based on the work of Evan Coury in his [zf-status](https://github.com/EvanDotPro/zf-status) project. This should help folks identify what new changes and features exist that they may want to review.

 If you missed the IRC meeting, we have a [summary posted](http://framework.zend.com/zf2/blog/entry/2011-08-17-IRC-Meeting-Log).

Development status
------------------

 The Zend Framework core team has decided to write, every week, a post on this blog to inform about the development status of ZF2.  
 The aim of this update is to have a new channel where the developers that are working on the ZF2 can share ideas with the community and inform people about the working progress of the project. Of course, we have the [ZF wiki](http://framework.zend.com/wiki/display/ZFDEV2/Home), the [mailing lists](http://framework.zend.com/wiki/display/ZFDEV/Contributing+to+Zend+Framework#ContributingtoZendFramework-Subscribetotheappropriatemailinglists) and the IRC channels (#zftalk.dev, #zf2-meeting) but we think that this blog can be helpful as well.  
 This is the first post of the new ZF2 blog about the dev status update of ZF2. We hope you will find it useful.

 In the last week the Zend Framework Core Team has been involved in the development of the new Zend\\Http components. In particular we have redesigned the following classes:

- Zend\\Http\\Request
- Zend\\Http\\Response
- Zend\\Http\\Headers
- Zend\\Http\\Client
 
  
 We implemented these components following the RFC specifications:  
  
- [RFC2616](http://tools.ietf.org/html/rfc2616), for the HTTP 1.1
- [RFC3986](http://tools.ietf.org/html/rfc3986), for the Uniform Resource Identifier (URI)
- [RFC2069](http://tools.ietf.org/html/rfc2069), [RFC2617](http://tools.ietf.org/html/rfc2617), for the HTTP Authentication: Basic and Digest Access Authentication
 
  
 The new API provided is more convenient compared with the ZF1. We provided a full OO implementation of the Headers with specific features for each type (for instance we have Zend\\Http\\Header\\Accept that implements the Accept header type).  
 These classes are implemented in the namespace Zend\\Http\\Header. In order to support not standard headers we built a generic header class, Zend\\Http\\Header\\GenericHeader.  The new Zend\\Http\\Client is basically a class that uses the Request, Response, Headers components to send request to a specific HTTP adapter.  
 Just to give you an idea of these new architecture, we reported an example of two different uses for the same use case:  
  
**First example**


    <pre class="highlight"><span style="color: #000000">
    <span style="color: #0000BB"><?php<br></br>    $client</span><span style="color: #007700">= new </span><span style="color: #0000BB">Zend</span><span style="color: #007700">\</span><span style="color: #0000BB">Http</span><span style="color: #007700">\</span><span style="color: #0000BB">Client</span><span style="color: #007700">(</span><span style="color: #DD0000">'http://www.test.com'</span><span style="color: #007700">);<br></br>    </span><span style="color: #0000BB">$client</span><span style="color: #007700">-></span><span style="color: #0000BB">setMethod</span><span style="color: #007700">(</span><span style="color: #DD0000">'POST'</span><span style="color: #007700">);<br></br>    </span><span style="color: #0000BB">$client</span><span style="color: #007700">-></span><span style="color: #0000BB">setParameterPost</span><span style="color: #007700">(array(</span><span style="color: #DD0000">'foo' </span><span style="color: #007700">=> </span><span style="color: #DD0000">'bar));<br></br>    <br></br>    $response= $client->send();<br></br>    <br></br>    if ($response->isSuccess()) {<br></br>        //  the POST was successfull<br></br>    }<br></br></span>
    </span>


 **Second example** 
    <pre class="highlight"><span style="color: #000000">
    <span style="color: #0000BB"><?php<br></br>    $request</span><span style="color: #007700">= new </span><span style="color: #0000BB">Zend</span><span style="color: #007700">\</span><span style="color: #0000BB">Http</span><span style="color: #007700">\</span><span style="color: #0000BB">Request</span><span style="color: #007700">();<br></br>    </span><span style="color: #0000BB">$request</span><span style="color: #007700">-></span><span style="color: #0000BB">setUri</span><span style="color: #007700">(</span><span style="color: #DD0000">'http://www.test.com'</span><span style="color: #007700">);<br></br>    </span><span style="color: #0000BB">$request</span><span style="color: #007700">-></span><span style="color: #0000BB">setMethod</span><span style="color: #007700">(</span><span style="color: #DD0000">'POST'</span><span style="color: #007700">);<br></br>    </span><span style="color: #0000BB">$request</span><span style="color: #007700">-></span><span style="color: #0000BB">setParameterPost</span><span style="color: #007700">(array(</span><span style="color: #DD0000">'foo' </span><span style="color: #007700">=> </span><span style="color: #DD0000">'bar));<br></br>    <br></br>    $client= new Zend\Http\Client();<br></br>    $response= $client->send($request);<br></br>    <br></br>    if ($response->isSuccess()) {<br></br>        //  the POST was successfull<br></br>    }<br></br></span>
    </span>


  
 Moreover, we implemented a static version of the Zend\\Http\\Client to be able to write something simple code like that:  

    <pre class="highlight"><span style="color: #000000">
    <span style="color: #0000BB"><?php<br></br>    $response</span><span style="color: #007700">= </span><span style="color: #0000BB">Zend</span><span style="color: #007700">\</span><span style="color: #0000BB">Http</span><span style="color: #007700">\</span><span style="color: #0000BB">ClientStatic</span><span style="color: #007700">::</span><span style="color: #0000BB">post</span><span style="color: #007700">(</span><span style="color: #DD0000">'http://www.test.com'</span><span style="color: #007700">,array(</span><span style="color: #DD0000">'foo'</span><span style="color: #007700">=></span><span style="color: #DD0000">'bar'</span><span style="color: #007700">));<br></br>    <br></br>    if (</span><span style="color: #0000BB">$response</span><span style="color: #007700">-></span><span style="color: #0000BB">isSuccess</span><span style="color: #007700">()) {<br></br>        </span><span style="color: #FF8000">//  the POST was successfull<br></br>    </span><span style="color: #007700">}<br></br></span>
    </span>