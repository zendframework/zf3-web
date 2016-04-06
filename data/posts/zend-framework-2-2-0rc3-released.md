---
layout: post
title: Zend Framework 2.2.0rc3 Released!
date: 2013-05-10 15:30
update: 2013-05-10 15:30
author: Matthew Weier O'Phinney
url_author: http://mwop.net/
permalink: /blog/zend-framework-2-2-0rc3-released.html
categories:
- blog
- released

---

 The Zend Framework community is pleased to announce the immediate availability of Zend Framework 2.2.0rc3! Packages and installation instructions are available at:

- [http://packages.zendframework.com/](https://packages.zendframework.com/)

 This is a _release candidate_. It is not the final release, and while stability is generally considered good, there may still be issues to resolve between now and the stable release. Use in production with caution.

 **DO** please test your applications on this RC, as we would like to ensure that it remains backwards compatible, and that the migration path is smooth.

Changes in this version
-----------------------

 Please see our [post for 2.2.0rc1](/blog/zend-framework-2-2-0rc1-released.html) and our [post for 2.2.0rc2](/blog/zend-framework-2-2-0rc1-released.html) for a list of changes. In addition to those changes, the following have been made:

- A late addition of `Zend\Stdlib\Hydrator\Aggregate` was made. This functionality allows the ability to map hydrators to objects via events, and generally streamlines the process of having a single hydrator for a hierarchy of objects. Read more in the [AggregateHydrator documentation](http://zf2.readthedocs.org/en/latest/modules/zend.stdlib.hydrator.aggregate.html).
- Improvements were made to `Zend\Di` to make it work better with the various "Aware" interfaces that have proliferated throughout the framework, eliminating issues where the component would attempt to instantiate an interface.

Changelog
---------

 Almost 200 patches were applied for 2.2.0. We will not release a full changelog until we create the stable release. In the meantime, you can view a full set of patches applied for 2.2.0 in the 2.2.0 milestone on GitHub:

- [Zend Framework 2.2.0 milestone](https://github.com/zendframework/zf2/issues?milestone=14&state=closed)

Thank You!
----------

 Please join me in thanking everyone who provided new features and code improvements for this upcoming 2.2.0 release!

Roadmap
-------

 This is the third release candidate. At this time, we anticipate a stable release sometime mid-week next week.

 Over the next few days, we will be expanding on documentation, and fixing any critical issues brought to our attention; we do not anticipate many, if any, critical issues at this time, however.

 Again, **DO** please test your applications on this RC, as we would like to ensure that it remains backwards compatible, and that the migration path is smooth.