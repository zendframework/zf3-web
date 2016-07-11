---
layout: post
title: Zend Framework 1.12.14, 2.4.6 and 2.5.2 Released!
date: 2015-08-03T21:15:00Z
update: 2015-08-03T21:15:00Z
author: Matthew Weier O'Phinney
url_author: https://mwop.net/
permalink: /blog/zend-framework-1-12-14-2-4-6-and-2-5-2-released.html
categories:
- blog
- released

---

 The Zend Framework community is pleased to announce the immediate availability of:

- Zend Framework **1.12.14**
- Zend Framework **2.4.6**
- Zend Framework **2.5.2**

- [http://framework.zend.com/downloads/latest](/downloads/latest)

 These releases contain a critical security fix.

Security Fix
------------

 Zend Framework versions 1.12.14, and 2.4.6, and 2.5.2 introduced fixes for [ZF2015-06](/security/advisory/ZF2015-06), a serious vulnerability in `ZendXml` when used under PHP-FPM to process multibyte XML documents. The advisory provides full details; if you process XML in your application and will be deploying or already deploy using PHP-FPM, we recommend upgrading immediately.

Other changes
-------------

 Zend Framework 1.12.14 has two other changes that may impact users:

- `Zend_Service_DeveloperGarden` was removed, as the service closed its API on 30 June 2015.
- `Zend_Service_Technorati` was removed, as the API has been unavailable for an indeterminate amount of time.

 Both Zend Framework 2.4.6 and 2.5.2 also incorporate a change in `Zend\InputFilter`; fixes done in the 2.4/2.5 series removed support for fallback values when performing validation; that support has been reinstated with the latest releases.

### Changelog

 For the full changelog on each version:

- [http://framework.zend.com/changelog/1.12.14](/changelog/1.12.14)
- [http://framework.zend.com/changelog/2.4.6](/changelog/2.4.6)
- [http://framework.zend.com/changelog/2.5.2](/changelog/2.5.2)

Long Term Support
-----------------

 As a reminder, the 2.4 series is our current Long Term Support release, and will receive security and critical bug fixes until 31 March 2018.

 You can opt-in to the LTS version by pinning your `zendframework/zendframework` [Composer](https://getcomposer.org) requirement to the version `~2.4.0`.

 [Visit our Long Term Support information page](/long-term-support) for more information.
