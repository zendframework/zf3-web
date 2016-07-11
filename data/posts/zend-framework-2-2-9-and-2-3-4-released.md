---
layout: post
title: Zend Framework 2.2.9 and 2.3.4 Released!
date: 2015-01-14T20:00:00Z
update: 2015-01-14T20:00:00Z
author: Matthew Weier O'Phinney
url_author: http://mwop.net/
permalink: /blog/zend-framework-2-2-9-and-2-3-4-released.html
categories:
- blog
- released

---

 The Zend Framework community is pleased to announce the immediate availability of:

- Zend Framework **2.2.9**
- Zend Framework **2.3.4**

- [http://framework.zend.com/downloads/latest](/downloads/latest)

 These are security releases; we strongly encourage users to upgrade.

Security Fix
------------

 One new security advisory has been made:

- [ZF2015-01](/security/advisory/ZF2015-01), which patches Zend\\Session's handling of session validators to ensure that any metadata they store in the session for validation of subsequent requests is properly persisted.

 For more information, follow the links above; if you use Zend\\Session validators, please upgrade immediately.

2.3.4
-----

 Th 2.3.4 release features over 200 patches, ranging from fixes in coding standards issues to the security patch listed above. For the full list of changes, visit the changelog:

- [Changelog](/changelog/2.3.4)

Thank You!
----------

 As usual, thanks go out to all contributors to these versions; Zend Framework's continued improvement is based on your efforts. I also want to thank [Marco Pivetta](https://github.com/ocramius) in particular, for the tireless effort he has made in triaging and merging pull requests for the 2.3.4 release; his efforts have been invaluable.
