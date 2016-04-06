---
layout: post
title: Zend Framework 2.2.7 and 2.3.1 Released!
date: 2014-04-15 22:05
update: 2014-04-15 22:05
author: Matthew Weier O'Phinney
url_author: http://mwop.net/
permalink: /blog/zend-framework-2-2-7-and-2-3-1-released.html
categories:
- blog
- released

---

 The Zend Framework community is pleased to announce the immediate availability of:

- Zend Framework **2.2.7**
- Zend Framework **2.3.1**

- [http://framework.zend.com/downloads/latest#ZF2](/downloads/latest#ZF2)

 While these are scheduled maintenance releases, they also contain important security fixes; we strongly encourage users to upgrade.

Security Fixes
--------------

 One new security advisory has been made, and has been patched in both 2.2.7 and 2.3.1.

 [ZF2014-03](/security/advisory/ZF2014-03), which mitigates potential cross site scripting (XSS) vectors in multiple view helpers due to inappropriate HTML attribute escaping. Many view helpers were using the escapeHtml() view helper in order to escape HTML attributes. This release patches them to use the escapeHtmlAttr() view helper in these situations. If you use form or navigation view helpers, or "HTML element" view helpers (such as gravatar(), htmlFlash(), htmlPage(), or htmlQuicktime()), we recommend upgrading immediately.

 For more information, follow the links above; if you use any of the components affected, please upgrade as soon as possible.

2.3.1
-----

 In addition to the security fixes listed above, **2.3.1** contains more than 80 bugfixes. In particular, a number of improvements were made to the behavior of nested form fieldsets and collection input filters (which often go hand-in-hand).

 For the complete list of changes, [read the changelog](/changelog/2.3.1).

Thank You!
----------

 As always, I'd like to thank the many contributors who made these releases possible! In particular, I'd like to thank the team at [Roave](https://roave.com), who both reported and patched the ZF2014-03 security issue.

Roadmap
-------

 Zend Framework 2 maintenance releases will happen bi-monthly, with the next one scheduled for mid-June, 2014. Releases may occur more frequently if security issues are reported.