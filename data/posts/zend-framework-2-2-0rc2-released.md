---
layout: post
title: Zend Framework 2.2.0rc2 Released!
date: 2013-05-07T00:00:00Z
update: 2013-05-07T00:00:00Z
author: Matthew Weier O'Phinney
url_author: https://mwop.net/
permalink: /blog/zend-framework-2-2-0rc2-released.html
categories:
- blog
- released

---

 The Zend Framework community is pleased to announce the immediate availability of Zend Framework 2.2.0rc2! Packages and installation instructions are available at:

- [http://packages.zendframework.com/](https://packages.zendframework.com/)

 This is a _release candidate_. It is not the final release, and while stability is generally considered good, there may still be issues to resolve between now and the stable release. Use in production with caution.

 **DO** please test your applications on this RC, as we would like to ensure that it remains backwards compatible, and that the migration path is smooth.

Changes in this version
-----------------------

 Please see our [post for 2.2.0rc1](/blog/zend-framework-2-2-0rc1-released.html) for a list of changes. In addition to those changes, the following have been made:

- A late change was made to eliminate and/or make optional several dependencies in `Zend\Feed` and `Zend\Validator`. While these are generally backwards compatible, we need to note that you can no longer directly use `Zend\I18n\Translator\Translator` with validators; instead, you must use `Zend\Mvc\I18n\Translator`. In most cases, this will not present an issue, as the translator object is generally injected via the `ValidatorPluginManager`, which has already been updated to inject the correct translator object.

 **_If you were manually injecting your validators with a translator object, please note that you must now use `Zend\Mvc\I18n\Translator`._**

 The changes have some immediate benefits: you can now use `Zend\Feed` with third-party HTTP clients!

Changelog
---------

 Almost 200 patches were applied for 2.2.0. We will not release a full changelog until we create the stable release. In the meantime, you can view a full set of patches applied for 2.2.0 in the 2.2.0 milestone on GitHub:

- [Zend Framework 2.2.0 milestone](https://github.com/zendframework/zf2/issues?milestone=14&state=closed)

Thank You!
----------

 Please join me in thanking everyone who provided new features and code improvements for this upcoming 2.2.0 release!

Roadmap
-------

 We plan to release additional RCs every 3-5 days until we feel the 2.2.0 release is generally stable; we anticipate a stable release sometime next week.

 During the RC period, we will be expanding on documentation, and fixing any critical issues brought to our attention.

 Again, **DO** please test your applications on this RC, as we would like to ensure that it remains backwards compatible, and that the migration path is smooth.
