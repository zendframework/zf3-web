---
layout: post
title: "Endings and Beginnings: Goodbye, and Please Welcome the Laminas Project!"
date: 2020-01-24T12:15:00-06:00
author: Matthew Weier O'Phinney
url_author: https://mwop.net
permalink: /blog/2020-01-24-laminas-launch
categories:
- apigility
- blog
- expressive
- laminas
- php
- zendframework
- linuxfoundation

---

**New Year's Eve 2019 marked a new era for Zend Framework.**

That day, the community migrated the Zend Framework code base to its new homes
on [GitHub](https://github.com), marking its shift to becoming the [Laminas
Project](https://getlaminas.org).

Up until that day, the Zend Framework was single-handedly sponsored and led by
[Zend Technologies](https://www.zend.com), and later [Rogue
Wave Software](https://www.roguewave.com). With the transition, it is now led by
an independent [Technical Steering Committee](https://github.com/laminas/technical-steering-committee),
and will soon be governed by a charter with the [Linux Foundation](https://www.linuxfoundation.org).

Over the years, Zend Framework has seen wide adoption across the PHP ecosystem,
with an emphasis on the Enterprise market. It has formed the basis of numerous
business applications and services including eCommerce platforms, content
management, healthcare systems, entertainment platforms and portals, messaging
services, APIs, and many others. **The Laminas Project will continue to serve PHP
users building these applications.**

## Changes

To prevent branding issues, the project has a new name, as does its subprojects.
At a glance:

Original Project | Original GitHub Organization | Original PHP Namespace | New Project Name | New GitHub Organization | New PHP Namespace
---------------- | ---------------------------- | ---------------------- | ---------------- | ----------------------- | -----------------
Zend Framework (components) | zendframework | `Zend` | Laminas (components) | [laminas](https://github.com/laminas) | `Laminas`
Zend Framework (MVC) | zendframework | `Zend` | Laminas (MVC) |  [laminas](https://github.com/laminas) | `Laminas`
Apigility | zfcampus | `ZF` / `ZF\Apigility` | Laminas API Tools | [laminas-api-tools](https://github.com/laminas-api-tools) | `Laminas\ApiTools`
Expressive | zendframework | `Zend\Expressive` | Mezzio | [mezzio](https://github.com/mezzio) | `Mezzio`

There are a few outliers (e.g., both zf-development-mode and
zf-composer-autoloading lived under zfcampus, and thus Apigility, previously,
but are now distributed as Laminas components), but those are the general
changes.

In all cases, the original repository was marked as "Archived" on GitHub, which
makes the repository read-only, and prevents opening new issues or pull
requests, or commenting on existing ones. Additionally, the related packages
were marked "Abandoned" on [Packagist](https://packagist.org), with a
recommendation of using the equivalent Laminas package.

**Users will still be able to install all existing Zend Framework, Apigility, and
Expressive packages**, now and into the foreseeable future; they just will not
get any more updates. As such, we strongly urge you to migrate your code to
Laminas.

## How to Migrate

The Laminas Project includes a [full migration guide](https://docs.laminas.dev/migration/)
which can be used both to migrate applications as well as libraries that consume
any Zend Framework, Apigility, and/or Expressive components.

## What's next?

For Zend Framework? Nothing. It's not going away, but there will be no further
development on it or its subprojects.

For Laminas? There are many ideas and projects that the community has been
waiting until the migration to start! Keep an eye on the [Laminas Project
blog](https://getlaminas.org/blog/) for new posts detailing these!
