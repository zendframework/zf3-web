---
layout: post
title: Zend Framework 2.4.4 Released!
date: 2015-07-21T18:45:00Z
update: 2015-07-21T18:45:00Z
author: Matthew Weier O'Phinney
url_author: https://mwop.net/
permalink: /blog/zend-framework-2-4-4-released.html
categories:
- blog
- released

---

 The Zend Framework community is pleased to announce the immediate availability of Zend Framework **2.4.4**. You can download it from the Zend Framework site:

- [http://framework.zend.com/downloads/latest](/downloads/latest)

 This is a [Long Term Support](/long-term-support/) release.

Bugfix
------

 This release contains a single critical bugfix. A [developer reported an issue against zend-stdlib](https://github.com/zendframework/zend-stdlib/pull/9) indicating that our count increment in `Zend\Stdlib\PriorityList` was incrementing incorrectly, and failing to take into account whether or not the item already was present.

 As this scenario affects usage of PriorityList with duplicate data, one of its specific use cases, we deemed the issue critical and backported the fix to the LTS release.
