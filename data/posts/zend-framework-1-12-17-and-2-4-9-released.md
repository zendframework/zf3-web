---
layout: post
title: Zend Framework 1.12.17 and 2.4.9 Released!
date: 2015-11-23T21:30:00Z
update: 2015-11-23T21:30:00Z
author: Matthew Weier O'Phinney
url_author: http://mwop.net/
permalink: /blog/zend-framework-1-12-17-and-2-4-9-released.html
categories:
- blog
- released

---

 The Zend Framework community is pleased to announce the immediate availability of:

- Zend Framework **1.12.17**
- Zend Framework **2.4.9**

- [http://framework.zend.com/downloads/latest](/downloads/latest)

 These releases contain security fixes.

Security Fixes
--------------

### ZF2015-09

 [ZF2015-09](/security/advisory/ZF2015-09) provides a security hardening patch for users of our word-based CAPTCHA adapters, ensuring better randomization of the letters generated.

 This particular issue touches each of the following projects, and was fixed in the versions specified:

- Zend Framework 1, version 1.12.17
- Zend Framework 2, versions 2.4.9
- zend-captcha, versions 2.4.9 and 2.5.2

### ZF2015-10

 [ZF2015-10](/security/advisory/ZF2015-10) addresses potential information disclosure for users of Zend Framework's `Zend\Crypt\PublicKey\Rsa` support, due to an insecure OpenSSL padding default. The issue is patched in Zend Framework 2.4.9 and zend-crypt 2.4.9/2.5.2.

Changelog
---------

 For the full changelog on each version:

- [http://framework.zend.com/changelog/1.12.17](/changelog/1.12.17)
- [http://framework.zend.com/changelog/2.4.9](/changelog/2.4.9)

Long Term Support
-----------------

 As a reminder, the 2.4 series is our current Long Term Support release, and will receive security and critical bug fixes until 31 March 2018.

 You can opt-in to the LTS version by pinning your `zendframework/zendframework` [Composer](https://getcomposer.org) requirement to the version `~2.4.0`.

 [Visit our Long Term Support information page](/long-term-support) for more information.
