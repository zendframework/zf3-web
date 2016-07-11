---
layout: post
title: Zend Framework 1.12.4, 2.1.6, and 2.2.6 Released!
date: 2014-03-07T00:00:00Z
update: 2014-03-07T00:00:00Z
author: Matthew Weier O'Phinney
url_author: http://mwop.net/
permalink: /blog/zend-framework-1-12-4-2-1-6-and-2-2-6-released.html
categories:
- blog
- released

---

 The Zend Framework community is pleased to announce the immediate availability of:

- Zend Framework **1.12.4**
- Zend Framework **2.1.6**
- Zend Framework **2.2.6**

- [http://framework.zend.com/downloads/latest](/downloads/latest)

 While these are scheduled maintenance releases, they also contain important security fixes; we strongly encourage users to upgrade.

Security Fixes
--------------

 Two new security advisories have been made:

- [ZF2014-01](/security/advisory/ZF2014-01), which mitigates XML eXternal Entity and XML Entity Expansion vectors in a variety of components. While we had taken measures two years ago to mitigate these issues, a researcher discovered several components that remained vulnerable.
- [ZF2014-02](/security/advisory/ZF2014-02), which mitigates an issue in our OpenID consumers whereby a malicious Identity Provider could be used to spoof the identity of other providers.

 For more information, follow the links above; if you use any of the components affected, please upgrade as soon as possible.

1.12.4
------

 This is the first maintenance release in almost a year on the 1.12 series, and contains fixes too numerous to list. Among some of the more important ones, however:

- The testing infrastructure has been upgraded to PHPUnit 3.7, making it far simpler for contributors to test changes.
- [\#221](https://github.com/zendframework/zf1/pull/221) removes the TinySrc view helper, as the TinySrc service no longer exists.
- [\#222](https://github.com/zendframework/zf1/pull/222) removes the InfoCard component, as the CardSpace service no longer exists.
- [\#271](https://github.com/zendframework/zf1/pull/271) removes the Nirvanix component, as the Nirvanix service shut down in October 2013.

 Many thanks to all the contributors who helped polish ZF1, including both Frank Brückner and Adam Lundrigan, who provided a ton of patches and feedback, and to Rob Allen, our release manager, for shepherding in contributions!

2.1.6
-----

 **2.1.6** is a security release only, and issued to provide fixes for [ZF2014-01](/security/advisory/ZF2014-01).

2.2.6
-----

 **2.2.6** is both a security and maintenance release. It addresses specifically [ZF2014-01](/security/advisory/ZF2014-01). Additionally, more than 100 patches were contributed to this release.

 For the complete list of changes, [read the changelog](/changelog/2.2.6).

ZendXml
-------

 We have released a new component, [ZendXml](https://github.com/zendframework/ZendXml), to help PHP developers mitigate XXE and XEE vectors in their own code. We highly recommend using it if you ware working with XML. It is available via Composer, as well as via [our packages site](https://packages.zendframework.com/).

Component Releases
------------------

The following components were updated, to the versions specified, to mitigate security issues.

- ZendOpenId, v2.0.2
- ZendRest, v2.0.2
- ZendService\_Amazon, v2.0.3
- ZendService\_Api, v1.0.0
- ZendService\_Audioscrobbler, v2.0.2
- ZendService\_Nirvanix, v2.0.2
- ZendService\_SlideShare, v2.0.2
- ZendService\_Technorati, v2.0.2
- ZendService\_WindowsAzure, v2.0.2

Thank You!
----------

 As always, I'd like to thank the many contributors who made these releases possible! The project is gaining in consistency and capabilities daily as a result of your efforts.

Roadmap
-------

 We plan to ship version 2.3.0 sometime next week (week of 10 March 2014). We will likely adopt a semi-monthly maintenance release schedule thereafter.
