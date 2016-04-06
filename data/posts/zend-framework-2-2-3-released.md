---
layout: post
title: Zend Framework 2.2.3 Released!
date: 2013-08-21 23:00
update: 2013-08-21 23:00
author: Matthew Weier O'Phinney
url_author: http://mwop.net/
permalink: /blog/zend-framework-2-2-3-released.html
categories:
- blog
- released

---

 The Zend Framework community is pleased to announce the immediate availability of Zend Framework 2.2.3! Packages and installation instructions are available at:

- [http://framework.zend.com/downloads/latest](/downloads/latest)

 This is the third monthly maintenance release in the 2.2 series.

Changelog
---------

 This release features over 25 changes. Some notable changes include:

- An update that ensures the filter and validator plugin managers are injected into the input filter factory when using the form factory. ([\#4851](https://github.com/zendframework/zf2/issues/4851))
- Fixes to code generation to ensure `use` statements are unique, and that non-namespaced class generation is possible. ([\#4988](https://github.com/zendframework/zf2/issues/4988) and [\#4990](https://github.com/zendframework/zf2/issues/4990))
- A fix to input filters and forms to ensure overwriting of inputs and input filters happens correctly. ([\#4996](https://github.com/zendframework/zf2/issues/4996))

 To see the full changelog, visit:

- [http://framework.zend.com/changelog/2.2.3](/changelog/2.2.3)

Thank You!
----------

 I'd like to thank everyone who provided issue reports, typo fixes, maintenance improvements, bugfixes, and documentation improvements; your efforts make the framework increasingly better!

Roadmap
-------

 Maintenance releases happen monthly on the third Wednesday.