---
layout: post
title: Zend Framework 2.3.9 and 2.4.2 Released!
date: 2015-05-11T20:00:00Z
update: 2015-05-11T20:00:00Z
author: Matthew Weier O'Phinney
url_author: https://mwop.net/
permalink: /blog/zend-framework-2-3-9-and-2-4-2-released.html
categories:
- blog
- released

---

 The Zend Framework community is pleased to announce the immediate availability of:

- Zend Framework **2.3.9**
- Zend Framework **2.4.2**

- [http://framework.zend.com/downloads/latest](/downloads/latest)

 These are the ninth and second feature releases, respectively, for these minor versions. The releases contain fixes for BC breaks introduced in 2.3.8 and 2.4.1.

Backwards Compatibility Fixes
-----------------------------

 Zend Framework versions 2.3.8 and 2.4.2 introduced fixes for [ZF2015-04](/security/advisory/ZF2015-04), a serious vulnerability in the `Zend\Mail` and `Zend\Http` components.

 Unfortunately, in fixing the security vulnerabilities, several use cases were broken, due to lack of tests covering the specific cases. These include:

- [Mail messages with multipart bodies were providing an incorrect header continuation.](https://github.com/zendframework/zf2/issues/7514)
- [Mail messages containing UTF-8 addresses were not being improperly tagged as invalid.](https://github.com/zendframework/zf2/issues/7506)
- [Cookies with array values were not being serialized and urlencoded, and thus were improperly tagged as invalid.](https://github.com/zendframework/zf2/issues/7507)

 The new releases fix these issues, ensuring that applications will be both protected from ZF2015-04, as well as continue to work under common use cases. Regression tests were added to ensure the functionality continues to work in the future.

### Changelog

 For the full changelog on each version:

- [http://framework.zend.com/changelog/2.4.2](/changelog/2.4.2)
- [http://framework.zend.com/changelog/2.3.9](/changelog/2.3.9)

Long Term Support
-----------------

 As a reminder, the 2.4 series is our current Long Term Support release, and will receive security and critical bug fixes until 31 March 2018.

 You can opt-in to the LTS version by pinning your `zendframework/zendframework` [Composer](https://getcomposer.org) requirement to the version `~2.4.0`.

 [Visit our Long Term Support information page](/long-term-support) for more information.

Roadmap
-------

 We are currently [shifting gears towards Zend Framework 3](/blog/announcing-the-zend-framework-3-roadmap.html) development.

Thank You!
----------

 I would like to thank [Maks3w](https://github.com/Maks3w) for assisting with triage and patching of these issues.
