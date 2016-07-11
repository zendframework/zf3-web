---
layout: post
title: Zend Framework 2.1.1 Released!
date: 2013-02-06T23:21:00Z
update: 2013-02-06T23:21:00Z
author: Matthew Weier O'Phinney
url_author: https://mwop.net/
permalink: /blog/zend-framework-2-1-1-released.html
categories:
- blog
- released

---

 The Zend Framework community is pleased to announce the immediate availability of Zend Framework 2.1.1! Packages and installation instructions are available at:

- [http://framework.zend.com/downloads/latest](/downloads/latest)

 Our contributors have been busy stress-testing the newly released 2.1.0, and we have a large number of improvements ready.

Changelog
---------

 This release includes almost 40 patches. These patches tidy up the 2.1 tree significantly, bringing fixes and improvements that greatly enhance the usability of the DB and Session components. The full list is as follows:

- [2510: Zend\\Session\\Container does not allow modification by reference](https://github.com/zendframework/zf2/issues/2510)
- [2899: Can't inherit abstract function Zend\\Console\\Prompt\\PromptInterface::show()](https://github.com/zendframework/zf2/issues/2899)
- [3455: Added DISTINCT on Zend\\Db\\Sql\\Select](https://github.com/zendframework/zf2/issues/3455)
- [3456: Connection creation added in Pgsql.php createStatement method](https://github.com/zendframework/zf2/issues/3456)
- [3608: Fix validate data contains arrays as values](https://github.com/zendframework/zf2/issues/3608)
- [3610: Form: rely on specific setter](https://github.com/zendframework/zf2/issues/3610)
- [3618: Fix bug when $indent have some string](https://github.com/zendframework/zf2/issues/3618)
- [3622: Updated Changelog with BC notes for 2.1 and 2.0.7](https://github.com/zendframework/zf2/issues/3622)
- [3623: Authentication using DbTable Adapter doesn't work for 2.1.0](https://github.com/zendframework/zf2/issues/3623)
- [3625: Missing instance/object for parameter route upgrading to 2.1.\*](https://github.com/zendframework/zf2/issues/3625)
- [3627: Making relative links in Markdown files](https://github.com/zendframework/zf2/issues/3627)
- [3629: Zend\\Db\\Select using alias in joins can results in wrong SQL](https://github.com/zendframework/zf2/issues/3629)
- [3638: Fixed method that removed part from parts in Mime\\Message](https://github.com/zendframework/zf2/issues/3638)
- [3639: Session Metadata and SessionArrayStorage requestaccesstime fixes.](https://github.com/zendframework/zf2/issues/3639)
- [3640: [#3625] Do not query abstract factories for registered invokables](https://github.com/zendframework/zf2/issues/3640)
- [3641: Zend\\Db\\Sql\\Select Fix for #3629](https://github.com/zendframework/zf2/issues/3641)
- [3645: Exception on destructing the SMTP Transport instance](https://github.com/zendframework/zf2/issues/3645)
- [3648: Ensure run() always returns Application instance](https://github.com/zendframework/zf2/issues/3648)
- [3649: Created script to aggregate return status](https://github.com/zendframework/zf2/issues/3649)
- [3650: InjectControllerDependencies initializer overriding an previously defined EventManager](https://github.com/zendframework/zf2/issues/3650)
- [3651: Hotfix/3650](https://github.com/zendframework/zf2/issues/3651)
- [3656: Zend\\Validator\\Db\\AbstractDb.php and mysqli](https://github.com/zendframework/zf2/issues/3656)
- [3658: Zend\\Validator\\Db\\AbstractDb.php and mysqli (issue: 3656)](https://github.com/zendframework/zf2/issues/3658)
- [3661: ZF HTTP Status Code overwritten](https://github.com/zendframework/zf2/issues/3661)
- [3662: Remove double injection in Plugin Controller Manager](https://github.com/zendframework/zf2/issues/3662)
- [3663: Remove useless shared in ServiceManager](https://github.com/zendframework/zf2/issues/3663)
- [3671: Hotfix/restful head identifier](https://github.com/zendframework/zf2/issues/3671)
- [3673: Add translations for Zend\\Validator\\File\\UploadFile](https://github.com/zendframework/zf2/issues/3673)
- [3679: remove '\\' character from Traversable ](https://github.com/zendframework/zf2/issues/3679)
- [3680: Zend\\Validator\\Db Hotfix (supersedes #3658)](https://github.com/zendframework/zf2/issues/3680)
- [3681: [#2899] Remove redundant method declaration](https://github.com/zendframework/zf2/issues/3681)
- [3682: Zend\\Db\\Sql\\Select Quantifier (DISTINCT, ALL, + Expression) support - supersedes #3455](https://github.com/zendframework/zf2/issues/3682)
- [3684: Remove the conditional class declaration of ArrayObject](https://github.com/zendframework/zf2/issues/3684)
- [3687: fix invalid docblock](https://github.com/zendframework/zf2/issues/3687)
- [3689: [#3684] Polyfill support for version-dependent classes](https://github.com/zendframework/zf2/issues/3689)
- [3690: oracle transaction support](https://github.com/zendframework/zf2/issues/3690)
- [3692: Hotfix/db parametercontainer mixed use](https://github.com/zendframework/zf2/issues/3692)

Thank You!
----------

 Many thanks to all contributors to this release!

Roadmap
-------

 Maintenance releases (typically) happen monthly on the third Wednesday. Despite the release today, we will still likely target a 2.1.2 release in two weeks.
