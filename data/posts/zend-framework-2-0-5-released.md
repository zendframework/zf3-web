---
layout: post
title: Zend Framework 2.0.5 Released!
date: 2012-11-25T16:00:00Z
update: 2012-11-25T16:00:00Z
author: Matthew Weier O'Phinney
url_author: http://mwop.net/
permalink: /blog/zend-framework-2-0-5-released.html
categories:
- blog
- released

---

 The Zend Framework community is pleased to announce the immediate availability of Zend Framework 2.0.5! Packages and installation instructions are available at:

- [http://framework.zend.com/downloads/latest](/downloads/latest)

Security Announcement
---------------------

 This release is a security release, and contains fixes to both the `Zend\Session\Validator\RemoteAddr` and `Zend\View\Helper\ServerUrl` classes. If you are using either, we recommend upgrading immediately. For more information, please read the [ZF2012-04 advisory details](/security/advisory/ZF2012-04). Thanks goes to Fabien Potencier for alerting us of the issues and working with us on appropriate fixes.

Changelog
---------

 In addition to the security fixes mentioned above, this release included five other patches, mostly trivial. The full list is as follows:

- [3004: Zend\\Db unit tests fail with code coverage enabled](https://github.com/zendframework/zf2/issues/3004)
- [3039: combine double if into single conditional](https://github.com/zendframework/zf2/issues/3039)
- [3042: fix typo 'consist of' should be 'consists of' in singular](https://github.com/zendframework/zf2/issues/3042)
- [3045: Reduced the #calls of rawurlencode() using a cache mechanism](https://github.com/zendframework/zf2/issues/3045)
- [3048: Applying quickfix for zendframework/zf2#3004](https://github.com/zendframework/zf2/issues/3048)
- [3095: Process X-Forwarded-For header in correct order](https://github.com/zendframework/zf2/issues/3095)

Thank You!
----------

 Many thanks to all contributors to this release!

Reminder
--------

 Maintenance releases happen monthly on the third Wednesday. Additionally, we have the next minor release, 2.1.0, slated for sometime next month.
