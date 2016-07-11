---
layout: post
title: Zend Framework 1.12.15 and 2.4.7 Released!
date: 2015-08-11T19:30:00Z
update: 2015-08-11T19:30:00Z
author: Matthew Weier O'Phinney
url_author: http://mwop.net/
permalink: /blog/zend-framework-1-12-15-and-2-4-7-released.html
categories:
- blog
- released

---

 The Zend Framework community is pleased to announce the immediate availability of:

- Zend Framework **1.12.15**
- Zend Framework **2.4.7**

- [http://framework.zend.com/downloads/latest](/downloads/latest)

Zend Framework 1.12.15
----------------------

 Zend Framework 1.12.15 contains several fixes to ensure backwards compatibility with previous releases as well as supported PHP versions:

- [\#591](https:/github.com/zendframework/zf1/pull/591) ensures that thet patch introduced to fix [ZF2015-06](/security/advisory/ZF2015-06) works for PHP 5.2 users.
- [\#587](https://github.com/zendframework/zf1/pull/587) fixes a regular expression in `Zend_Http_Response::extractHeaders()` to ensure it will work with any valid header name, as well as empty header values.
- [\#597](https://github.com/zendframework/zf1/pull/597) updates `Zend_Http_Client_Adapter_Curl` to ensure it properly distinguishes between the `timeout` and `request_timeout` options, using them to set `CURLOPT_CONNECTTIMEOUT` and `CURLOPT_TIMEOUT`, respectively.

 For a full list of changes, see:

- [http://framework.zend.com/changelog/1.12.15](/changelog/1.12.15)

Zend Framework 2.4.7
--------------------

 Zend Framework 2.4.7 has a single change:

- [zend-inputfilter #15](https://github.com/zendframework/zend-inputfilter/pull/15) ensures that input filters can validate not just arrays, but objects implementing `ArrayAccess`, a scenario that broke with fixes introduced for 2.4.5.

Long Term Support
-----------------

 As a reminder, the 2.4 series is our current Long Term Support release, and will receive security and critical bug fixes until 31 March 2018.

 You can opt-in to the LTS version by pinning your `zendframework/zendframework` [Composer](https://getcomposer.org) requirement to the version `~2.4.0`.

 [Visit our Long Term Support information page](/long-term-support) for more information.
