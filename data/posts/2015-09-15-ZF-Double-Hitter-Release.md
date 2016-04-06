---
layout: post
title: Zend Framework 1.12.16 and 2.4.8 Released!
date: 2015-09-15
author: Matthew Weier O'Phinney
url_author: http://mwop.net/
permalink: /blog/zend-framework-1-12-16-and-2-4-8-released.html
categories:
- blog
- released

---

 The Zend Framework community is pleased to announce the immediate availability of:

- Zend Framework **1.12.16**
- Zend Framework **2.4.8**

- [http://framework.zend.com/downloads/latest](/downloads/latest)

 These releases contain a security fixes.

<!--more-->

Security Fixes
--------------

### ZF2015-07

 [ZF2015-07](/security/advisory/ZF2015-07) addresses attack vectors that arise due to incorrect permissions masks when creating directories and files within library code.

 This particular issue touches each of the following projects, and was fixed in the versions specified:

- Zend Framework 1, version 1.12.16
- Zend Framework 2, versions 2.4.8
- zf-apigility-doctrine, version 1.0.3
- zend-cache, versions 2.4.8 and 2.5.3

### ZF2015-08

 [ZF2015-08](/security/advisory/ZF2015-08) addresses potential null byte injection of SQL statements issued using Zend Framework's pdo\_dblib (FreeTDS) and pdo\_sqlite adapters. The issue is patched in Zend Framework 1.12.16.

Changelog
---------

 For the full changelog on each version:

- [http://framework.zend.com/changelog/1.12.16](/changelog/1.12.16)
- [http://framework.zend.com/changelog/2.4.8](/changelog/2.4.8)

 In particular, the 2.4.8 release has numerous fixes in the InputFilter, Validator, and Form components introduced to increase stability and reinstate behavior prior to version 2.4.0. At this time, forms and input filters created using code from pre-2.4 should work identically.

 We have, however, _deprecated_ the `allow_empty` and `continue_if_empty` flags, and provided notes in the changelog that describe alternatives to their usage. We have found that these flags, particularly in combination with the `required` flag and validators, can lead to unexpected or unintended behavior, often contradictory. Deprecating them will allow us to introduce cleaner solutions in future releases.

Long Term Support
-----------------

 As a reminder, the 2.4 series is our current Long Term Support release, and will receive security and critical bug fixes until 31 March 2018.

 You can opt-in to the LTS version by pinning your `zendframework/zendframework` [Composer](https://getcomposer.org) requirement to the version `~2.4.0`.

 [Visit our Long Term Support information page](/long-term-support) for more information.
