---
layout: post
title: Zend Framework 2.4.3 Released!
date: 2015-06-18T16:30:00Z
update: 2015-06-18T16:30:00Z
author: Matthew Weier O'Phinney
url_author: https://mwop.net/
permalink: /blog/zend-framework-2-4-3-released.html
categories:
- blog
- released

---

 The Zend Framework community is pleased to announce the immediate availability of Zend Framework **2.4.3**. You can download it from the Zend Framework site:

- [http://framework.zend.com/downloads/latest](/downloads/latest)

 This is a [Long Term Support](/long-term-support/) release.

Bugfix
------

 This release contains a single critical bugfix. A [developer reported an issue against zend-view](https://github.com/zendframework/zend-view/pull/4) indicating that when using port forwarding, and particularly when combined with non-standard ports, the `ServerUrl` view helper was incorrectly generating URIs containing both the local port and the public port. As an example, a server running on port 10081, but accessed via port 10088 was reporting a URI in the form "localhost:10088:10081". For purposes of public links, the public port 10088 _only_ should be present in the generated URI.

 As this scenario is common when using Vagrant, we deemed the issue critical and backported the fix to the LTS release.
