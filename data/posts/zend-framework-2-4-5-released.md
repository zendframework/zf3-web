---
layout: post
title: Zend Framework 2.4.5 Released!
date: 2015-07-28T18:45:00Z
update: 2015-07-28T18:45:00Z
author: Matthew Weier O'Phinney
url_author: http://mwop.net/
permalink: /blog/zend-framework-2-4-5-released.html
categories:
- blog
- released

---

 The Zend Framework community is pleased to announce the immediate availability of Zend Framework **2.4.5**. You can download it from the Zend Framework site:

- [http://framework.zend.com/downloads/latest](/downloads/latest)

 This is a [Long Term Support](/long-term-support/) release.

Bugfix
------

 This release contains a single critical bugfix. A [developer reported an issue against zend-inputfilter](https://github.com/zendframework/zend-inputfilter/pull/7), indicating that the combination of _required_ and _allow\_empty_ was not working as expected. When the given input was missing from the submitted data set, the set was still considered valid, when it should not be. When the value was present but empty, validation worked as expected.

 We supplied a patch to ensure behavior is as expected. The patch is also applied to zend-inputfilter 2.5.2.

 As this scenario affects a common use case for input validation, we deemed the issue critical and backported the fix to the LTS release.
