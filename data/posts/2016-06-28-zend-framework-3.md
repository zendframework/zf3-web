---
layout: post
title: Zend Framework 3 Released!
date: 2016-06-28T08:45:00-05:00
update: 2016-06-28T08:45:00-05:00
author: Matthew Weier O'Phinney
url_author: https://mwop.net/
permalink: /blog/2016-06-28-zend-framework-3.html
categories:
- blog

---

After 17 months of effort, hundreds of releases, tens of thousands of commits by
hundreds of contributors, and [millions of installs](/stats), we're pleased to
announce the immediate availability of **Zend Framework 3**.

What is Zend Framework 3?

For Zend Framework 2 MVC users, the differences are subtle:

- Increased performance; we've measured up to 4X faster applications under PHP
  5, and even better performance under PHP 7!
- PHP 7 support.
- A focus on de-coupling packages, to allow re-use in a greater number of
  contexts. In some cases, this has meant the creation of new packages that
  either split concerns, or provide integration between multiple components.
- A focus on documentation. Documentation is now included within each component
  repository, allowing us to block contributions for lack of documentation, as
  well as automate deployment of documentation. See our new
  [documentation site](https://docs.zendframework.com/) for the results.

Migration from version 2 to version 3 was at the top of our minds, and we have
provided a number of forwards compatibility features over the course of ZF3
development, and written [migration guides](https://docs.zendframework.com/tutorials/migration/to-v3/overview/)
to help you navigate the changes.

If you are already familiar with our MVC, or want to get started with it, we
have created a new version of the [skeleton application](https://github.com/zendframework/ZendSkeletonApplication)
that ships with minimal dependencies, and provides a number of convenience
features including the ability to select optional packages at installation, as
well as auto-register components and modules when adding them to your
application. [Read more about the skeleton in the documentation.](https://docs.zendframework.com/tutorials/getting-started/skeleton-application/)

For newcomers to the framework, we have been working on our package
architecture, and attempting to make each package installable with a minimal
amount of dependencies, to allow usage in any project, from Zend Framework MVC
applications to other popular frameworks such as Laravel and Symfony. All
components are now developed independently, with their own release schedules,
allowing us to ship bugfixes and new features more frequently. *This change has
allowed us to tag multiple hundreds of releases in the past year!*

The Zend Framework 3 initiatives also included a number of new features,
primarily around [PSR-7](http://www.php-fig.org/psr/psr-7/) (HTTP Message
interfaces) support. These include:

- [Diactoros](https://docs.zendframework.com/zend-diactoros/), the original and
  leading PSR-7 implementation in the PHP ecosystem.
- [Stratigility](https://docs.zendframework.com/zend-stratigility/), a PSR-7
  middleware foundation based on the node.js [Sencha Connect](https://github.com/senchalabs/connect).
- [Expressive](https://docs.zendframework.com/zend-expressive/), a PSR-7
  middleware microframework.

Yes, you read that correctly: Zend Framework now ships with a microframework as
a parallel offering to its MVC full-stack framework! For users new to Zend
Framework who are looking for a place to dive in, we recommend Expressive, as we
feel PSR-7 middleware represents the future of PHP application development.

The release today is a new beginning for the framework, returning to its
original mission: a strong component library, with opt-in MVC features.

Join our community today; we're available on the
[#zftalk Freenode IRC channel](irc://irc.freenode.net:6697/zftalk), and via our
[component repositories](/learn) (for discussing issues and development).

&mdash; The Zend Framework Team &mdash;

*Look for follow-up posts on this blog soon, detailing some of the new
features!*
