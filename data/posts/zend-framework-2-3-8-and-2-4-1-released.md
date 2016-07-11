---
layout: post
title: Zend Framework 2.3.8 and 2.4.1 Released!
date: 2015-05-07T23:00:00Z
update: 2015-05-07T23:00:00Z
author: Matthew Weier O'Phinney
url_author: http://mwop.net/
permalink: /blog/zend-framework-2-3-8-and-2-4-1-released.html
categories:
- blog
- released

---

 The Zend Framework community is pleased to announce the immediate availability of:

- Zend Framework **2.3.8**
- Zend Framework **2.4.1**

- [http://framework.zend.com/downloads/latest](/downloads/latest)

 These are the eighth and first feature releases, respectively, for these minor versions. The releases contain an important security fix; 2.4.1 also corrects several backwards compatibility (BC) breaks introduced in version 2.4.0.

Security Fix
------------

 One new security advisory has been made:

- [ZF2015-04](/security/advisory/ZF2015-04), which patches CRLF Injection Attacks in the Zend\\Mail and Zend\\Http components.

 If you use either component, either standalone or as part of other components such as Zend\\Mvc, we recommend that you upgrade immediately to either 2.3.8 or 2.4.1. Read the linked advisory for full details.

Bugfixes
--------

 Several important fixes are present in 2.4.1 were made that correct backwards compatibility breaks introduced in 2.4.0. These include:

- [[#7422]](https://github.com/zendframework/zf2/pull/7422) fixes a regression in Zend\\Db\\Sql\\Expression whereby placeholders were being double percent-encoded.
- [[#7426]](https://github.com/zendframework/zf2/pull/7426) fixes a regression in Zend\\Form whereby input filters attached to collections were no longer being added to the form, leading to incorrect validation and the inability to bind data to nested fieldsets.
- [[#7446]](https://github.com/zendframework/zf2/pull/7446) fixes a regression in Zend\\Form with regards to removal of multiple elements at once.
- [[#7474]](https://github.com/zendframework/zf2/pull/7474) fixes a regression in Zend\\InputFilter which broke the relationship between required inputs that were allowed empty, leading to false identification of invalid inputs.

### Changelog

 The above are only selected changes and features. For the full changelog on each version:

- [http://framework.zend.com/changelog/2.4.1](/changelog/2.4.1)
- [http://framework.zend.com/changelog/2.3.8](/changelog/2.3.8)

Long Term Support
-----------------

 As a reminder, the 2.4 series is our current Long Term Support release, and will receive security and critical bug fixes until 31 March 2018.

 You can opt-in to the LTS version by pinning your zendframework/zendframework [Composer](https://getcomposer.org) requirement to the version ~2.4.0.

 [Visit our Long Term Support information page](/long-term-support) for more information.

Roadmap
-------

 We are currently [shifting gears towards Zend Framework 3](/blog/announcing-the-zend-framework-3-roadmap.html) development.

 Tomorrow, 8 May 2015, we will be starting the process of splitting all components into their own repositories, to be developed on their own life cycles. Once that process is complete, we'll tag each at 2.5.0, and modify the primary Zend Framework repository to act primarily as a metapackage that pulls in each component. We also will be adding some new components next week (week of 11 May 2015) that will demonstrate future direction of the framework.

Thank You!
----------

 We had a number of excellent issue reports and patches submitted for this release. In particular, I would like to thank [Maks3w](https://github.com/Maks3w) for reviewing and tagging issues and patches for the release, as well as his enormous assistance with the ZF2015-04 patches.
