---
layout: post
title: Zend Framework 1.12.11 Released!
date: 2015-02-11T18:00:00Z
update: 2015-02-11T18:00:00Z
author: Matthew Weier O'Phinney
url_author: http://mwop.net/
permalink: /blog/zend-framework-1-12-11-released.html
categories:
- blog
- released

---

 The Zend Framework community is pleased to announce the immediate availability of:

- Zend Framework **1.12.11**

You can download Zend Framework at:

- [http://framework.zend.com/downloads/latest#ZF1](/downloads/latest#ZF1)

Fixes
-----

 The primary rationale for the release was a problem introduced by a bugfix in 1.12.10 with regards to the `ViewRenderer` action helper. The fix was incorrectly resolving the controller name, which led to problems primarily when using a custom dispatcher with your application. 1.12.11 introduces a proper fix that addresses the original issue, as well as the problem it introduced.

 For the full list of changes, visit the changelog:

- [Changelog](/changelog/1.12.11)
