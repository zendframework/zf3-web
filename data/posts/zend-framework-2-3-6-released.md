---
layout: post
title: Zend Framework 2.3.6 Released!
date: 2015-03-12 16:00
update: 2015-03-12 16:00
author: Matthew Weier O'Phinney
url_author: http://mwop.net/
permalink: /blog/zend-framework-2-3-6-released.html
categories:
- blog
- released

---

 The Zend Framework community is pleased to announce the immediate availability of:

- Zend Framework **2.3.6**

- [http://framework.zend.com/downloads/latest](/downloads/latest)

 This is a security release.

Security Fix
------------

 One new security advisory has been made:

- [ZF2015-03](/security/advisory/ZF2015-03), which patches Zend\\Validator\\Csrf to ensure proper identification of null and malformed token identifiers.

 For more information, follow the links above; if you use Zend\\Validator\\Csrf, either standalone or with Zend\\InputFilter or Zend\\Form\\Element\\Csrf, with the 2.3 series of Zend Framework, please upgrade immediately.

Support Zend Framework!
-----------------------

 Sitepoint is currently running a [ "Best PHP Framework 2015 Survey"](http://www.sitepoint.com/best-php-framework-2015-survey/); we kindly ask that you help represent the Zend Framework community in the survey, and show your support!

Milestones
----------

 We are currently actively finishing the final features for Zend Framework 2.4, and plan a release candidate for next week (week of 17 March 2015). Once 2.4 stable is released, we turn towards Zend Framework 3 tasks, as outlined in [ the Zend Framework 3 roadmap](/blog/announcing-the-zend-framework-3-roadmap.html).