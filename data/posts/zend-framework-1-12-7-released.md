---
layout: post
title: Zend Framework 1.12.7 Released!
date: 2014-06-12T22:00:00Z
update: 2014-06-12T22:00:00Z
author: Matthew Weier O'Phinney
url_author: https://mwop.net/
permalink: /blog/zend-framework-1-12-7-released.html
categories:
- blog
- released

---

 The Zend Framework community is pleased to announce the immediate availability of Zend Framework 1.12.7:

- [http://framework.zend.com/downloads/latest#ZF1](/downloads/latest#ZF1)

 This release contains an important security fix in `Zend_Db_Select`; we strongly encourage users of this component to upgrade.

Security Fixes
--------------

 One new security advisory has been made, and has been patched in 1.12.7:

 [ZF2014-04](/security/advisory/ZF2014-04), which mitigates a potential SQL Injection (SQLi) vector when usiing ORDER BY clauses in Zend\_Db\_Select; SQL function calls were improperly detected, rendering ORDER clauses such as MD5(1);drop table foo unfiltered. The logic has been updated to prevent SQLi vectors, and users of this functionality are strongly encouraged to upgrade immediately.

 For more information, follow the link above; if you use the component affected, please upgrade as soon as possible.

Important Changes
-----------------

 In addition to the security fix above, a number of other important changes were made, including:

- Support for PHPUnit 4 and 4.1, both within the Zend Framework test suite and inside the Zend\_Test\_PHPUnit component.
- Backported support from ZF2 for recursive page removal within Zend\_Navigation.
- Support within the Hostname validator for the newly released IANA top level domains.
- Forward-compatibility changes were made to ensure Zend Framework 1 will run on the upcoming PHP 5.6.

 For the complete list of changes, [read the changelog](/changelog/1.12.7).

Thank You!
----------

 As always, I'd like to thank the many contributors who made this release possible, particularly Cassiano Dal Pizzol and Lars Kneschke for reporting the security vulnerability, and Enrico Zimuel for patching it.
