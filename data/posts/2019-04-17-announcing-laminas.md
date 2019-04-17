---
layout: post
title: From Zend to Laminas
6ate: 2019-04-17T09:00:00-05:00
author: Matthew Weier O'Phinney
url_author: https://mwop.net
permalink: /blog/2019-04-17-announcing-laminas.html
categories:
- apigility
- blog
- expressive
- laminas
- php
- zendframework
- linuxfoundation

---

Since its inception, [Zend Technologies](https://www.zend.com), and later [Rogue
Wave Software](https://www.roguewave.com), has been single-handedly leading and
sponsoring the Zend Framework project. Over the years, Zend Framework has seen
wide adoption across the PHP ecosystem, with an emphasis on the Enterprise
market. It has formed the basis of numerous business application and services
including eCommerce platforms, content management, healthcare systems,
entertainment platforms and portals, messaging services, APIs, and many others.

> To take it to the next step of adoption and innovation, we are happy to
> announce that we are transitioning Zend Framework and all its subprojects to an open
> source project hosted at the [Linux Foundation](https://www.linuxfoundation.org).  

The Linux Foundation is host to a range of widely successful open source
projects, and has led many similar transitions in the past. We believe that by
moving Zend Framework to this proven governance model, it will enjoy both growth
in adoption and contributors, and it will continue to focus on delivering best
of breed innovative code, using the highest standards of security, transparency,
and quality. 

Please welcome [The Laminas Project](https://getlaminas.org)!

> The transition is already underway, but that we are working to finalize the
> list of founding members. [Contact us to learn more about membership benefits
> and how your company can help guide the project
> roadmap.](https://getlaminas.org/about/join)

## What is Laminas?

In spinning the project into its own foundation, we need to differentiate the
Open Source project from the commercial Zend brand, which means a new name!

**Laminas** is the plural of **lamina**, meaning _a thin layer_. We feel it
succinctly summarizes the goals of the project in many ways:

- Components you can compose or _layer_ into any application.
- Middleware architectures are often termed _layered_.

The project will encompass each of the following:

- The various standalone components.
- The MVC framework.
- The Apigility subproject.
- The Expressive subproject.

## Governance

The porject will be led by a **Governing Board**, responsible for the business
decisions, and a **Technical Steering Committee**, responsible for the technical
decisions.

The Technical Steering Committee will initially be composed by current members
of the Zend Framework Community Review Team, along with other folks who are
helping us during the transition. Its role will be to make decisions about what
we maintain, what milestones to work on, who has commit access to specific
repositories, and the general technical direction of the project.

The Governing Board will consist of representatives from member companies, as
well as one or more TSC members. The Board will be responsible for establishing
the governance structure and general project oversight including business
decisions, budgets, organizing events and working groups, providing marketing
support to the project, and handling IP or legal issues as they arise.

The foundation will also eventually include a small team of developers to help
lead the day-to-day maintenance, manage automation, keep the web presence
online, and other tasks necessary to keeping the project going. This team will
help provide project continuity, allowing contributors to focus on the
contributions they care about most.

## What will happen to the code?

Components and the MVC code will move to a new Laminas organization under
GitHub. Apigility packages will move from the zfcampus organization to the
Apigility organization. Expressive packages will move to a new Expressive
organization.

The existing code will all be archived. This means it will remain available on
GitHub, but will be read-only; this will allow existing installations to
continue working without interruption, but clearly signal that development has
moved to the new project. The related package entries on Packagist will each be
marked as deprecated, and point to the relevant new Laminas package as an
alternative.

All packages published by the project will be marked as _replacements_ of the
existing Zend Framework packages, and will include tools to alias legacy classes
to the new package classes. This will allow for seamless integration in existing
projects, including when using third-party libraries that leverage ZF code.

Finally, we will be providing tools both to update your own code to use the new
classes provided by Laminas packages, as well as to help you update your
dependencies to use the new Laminas packages instead of the Zend Framework
versions. This latter will likely be accomplished via a
[Composer](https://getcomposer.org) plugin to help automate the transition.

## Timeline

Much of the transition is well under way at this point with domains secured,
GitHub organizations created, initial sponsorship commitments in place, and
migration tooling being tested. Our hope is to be operational within Q2 or Q3 of
this year (2019); the timeline will be based on how quickly we can secure
sponsorship commitments for the foundation.

If you are interested in becoming a founding member, please visit [the Laminas
Project website](https://getlaminas.org) and fill out the form there.
