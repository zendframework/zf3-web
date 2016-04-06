---
layout: post
title: Zend Framework 2.2.2 Released!
date: 2013-07-24 19:00
update: 2013-07-24 19:00
author: Matthew Weier O'Phinney
url_author: http://mwop.net/
permalink: /blog/zend-framework-2-2-2-released.html
categories:
- blog
- released

---

 The Zend Framework community is pleased to announce the immediate availability of Zend Framework 2.2.2! Packages and installation instructions are available at:

- [http://framework.zend.com/downloads/latest](/downloads/latest)

 This is the second monthly maintenance release in the 2.2 series.

Changelog
---------

 This release features over 60 changes. Some notable changes include:

- The cURL adapter for `Zend\Http` will no longer double-decode gzip-encoded bodies. ([\#4555](https://github.com/zendframework/zf2/issues/4555))
- A `headLink()` method was added to the HeadLink view helper so that its usage matches the documentation. ([\#4105](https://github.com/zendframework/zf2/issues/4105))
- The validator plugin manager was updated to include the new "PhoneNumber" validator. ([\#4644](https://github.com/zendframework/zf2/issues/4644))
- Abstract methods in the `AbstractRestfulController` were made non-abstract, and modified to set a 405 ("Method Not Allowed") status. ([\#4808](https://github.com/zendframework/zf2/issues/4808))

 To see the full changelog, visit:

- [http://framework.zend.com/changelog/2.2.2](/changelog/2.2.2)

Thank You!
----------

 I'd like to thank everyone who provided issue reports, typo fixes, maintenance improvements, bugfixes, and documentation improvements; your efforts make the framework increasingly better!

Roadmap
-------

 Maintenance releases happen monthly on the third Wednesday. Version 2.3.0 is tentatively scheduled for September.