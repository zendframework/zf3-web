---
layout: case_study
title: VuFind
date: 2018-08-17T10:38:00-05:00
author: Demian Katz
url_author: https://vufind.org
permalink: /case-studies/vufind.html
categories:
- case_study
- framework

---

# Case Study: VuFind

## Background

[VuFind](https://vufind.org) is an open source search abstraction layer used primarily by libraries and other cultural heritage institutions, but also adaptable to many other applications where a flexible search engine is needed. The software provides a largely configuration-driven and highly extensible approach to building a search system, allowing a common interface to be applied to multiple backend systems. The most common application is as a front end for [Apache Solr](http://lucene.apache.org/solr/), but the software can also connect to a variety of third-party search APIs, etc.

VuFind was originally written as a stand-alone model-view-controller application using a homegrown framework, but it was completely rewritten during 2011 and 2012 to use Zend Framework (then version 2), in order to improve extensibility and better follow community standards.

The VuFind project has a fairly informal structure, with an overall project lead developer (following the "benevolent dictator" model), a user interface lead designer, a small team of 3-5 core contributors, and a long tail of dozens of occasional contributors. Over its lifetime, hundreds of people have touched the code in one way or another, the lead developer has changed once, and the core team has shifted significantly over time.

## Requirements

VuFind had several key requirements that had to be taken into account when selecting a framework:

  * **Extensibility**: VuFind is widely adopted and covers a broad range of use cases. Users need the ability to customize and override nearly every part of the software. Ideally, customizations should be achieved by adding custom subclasses without having to modify any core code. The *zend-mvc* and *zend-servicemanager* components provide a perfect mechanism for customization, since all core logic can be built as overrideable services, and plugin managers can be used to create configurable extensions. The *zend-modulemanager* provides a way to encapsulate custom code and manage it separately from the VuFind core.
  * **Custom Themes**: Just as users may need to override nearly any piece of code, they also need to override individual view templates. With *zend-view*, VuFind is able to provide simple PHP templates that, with the help of some VuFind-specific listeners and view helpers, can be organized into configurable themes that inherit from each other, allowing a user to override only the files that need to be changed.
  * **Multitenancy**: Some institutions use a single installation of VuFind to manage multiple independently customized sites. By using environment variables to control which modules and configurations are loaded, the extensibility and theming features listed above, combined with a bit of clever Apache configuration, enable multitenancy.
  * **Event Management**: Because it manages complex search processes, VuFind users sometimes need a way to inject parameters and/or manipulate result objects while processing is underway. The *zend-eventmanager* offers a mechanism for providing customization hooks for these types of processes that do not require procedural code to be rewritten or customized.
  * **Internationalization**: VuFind is used all over the world, so a translation layer is a necessity; *zend-i18n* meets this need well, allowing custom language file loaders and text-domain-based organization to simplify the management of translations.
  * **Command Line Utilities**: While VuFind is primarily a web application, it also uses a variety of command-line tools to perform maintenance tasks such as indexing records and managing Solr. The *zend-console* library allows command-line tools to be built that share common logic with the web application.
  * **Code Generation**: With its many customization options, automatic code/configuration generators can save users a great deal of time. VuFind's generators are powered by *zend-code*.
  * **Database Abstraction**: VuFind is designed to work with multiple relational database backends (primarily for storing user-generated content). The *zend-db* module provides a flexible and powerful mechanism to interact with a database when a full ORM like Doctrine is not necessary.
  * **Robust HTTP Interactions**: Because VuFind connects to a wide variety of APIs, it needs to make many different HTTP requests, originating from a variety of different server and network environments; *zend-http* offers the flexibility needed to make this work.
  * **Logging**: VuFind administrators and developers need logging to debug code in development and to monitor systems in production. The *zend-log* module allows a great deal of flexibility to meet these varied use cases.
  * **...and the rest**: VuFind also leverages a variety of Zend Framework components for other aspects of its functionality: *zend-config* for configuration management, *zend-feed* for RSS feed generation, *zend-mail* for email and text message sending, and *zend-paginator* for result pagination, among many others.

## Benefits of Zend Framework

While many of the needs listed above are fairly generic and could potentially be met by many frameworks, Zend Framework's strong support for extensibility through *zend-servicemanager* really set it apart, especially at the time VuFind was looking for a framework to standardize on. Over the years of its development, Zend Framework has allowed VuFind to meet or exceed its goals for customization capabilities while also encouraging and supporting good design practices such as separation of concerns.

## Evolution of Zend Framework

When VuFind adopted Zend Framework 2, the framework was a monolithic package containing many components. This encouraged VuFind to prefer Zend components for everything. Now that the framework has split into independent components, users have greater choice -- though so far, there has been no compelling reason to abandon a Zend component for a different one, even though VuFind has kept up to date with newer ZF releases.

Some component updates (such as major releases of *zend-mvc* and *zend-servicemanager*) have been fairly disruptive, requiring significant work to update the VuFind application for compatibility. However, these changes have had clear motivations and the process of reaching compatibility has generally led to cleaner, easier to maintain code on the VuFind side.

## Shortcomings of Zend Framework

The VuFind community's experience with Zend Framework has been overwhelmingly positive thus far. If one problem had to be identified at the moment, it would probably be the software's use of *zend-mvc* controllers and routes. As of this writing, many of VuFind's controllers are  quite heavy-weight, with many dependencies and too much logic per class; configuration for routes is so verbose and repetitive that VuFind includes code to dynamically generate it, which makes some of the configurations more readable but harder to override.

These problems (especially the heavy controllers) are not entirely Zend Framework's fault, but they are symptoms of some anti-patterns that were encouraged by early releases of Zend Framework 2, such as overuse of auto-injected service managers and reliance on "magic" controller plugins. These practices have been appropriately discouraged in newer Zend Framework releases, but their use during early VuFind framework development introduced technical debt that will take some time to pay off.

Zend Expressive offers some interesting alternative approaches to managing web applications, and VuFind may eventually investigate whether its controllers could be replaced with middleware. However, for the moment, its current architecture continues to meet its community's needs, and incremental progress is planned for resolving existing technical debt.

Outside of this fairly specific complaint, the biggest criticism of Zend Framework is its relatively steep learning curve; understanding verbose configuration files and specific design patterns is critical to effectively modifying the code. New members of the community need some hand-holding to get up to speed. However, because of the way the framework helps enforce separation of concerns and extensibility, the cost seems justified by the benefits. Before the framework, developers could dive in and make changes more quickly, but the resulting code often lacked structure and led to problems with maintainability. With Zend Framework, these issues have been significantly reduced.

## Conclusions

Adopting Zend Framework to replace a homegrown framework was unquestionably a good decision, adding a lot of power and functionality to VuFind and inspiring design improvements year after year. With the rise of [Composer](https://getcomposer.org), users now have a lot more choice in selecting PHP components, and the MVC model is no longer the uncontested ruler of the web space, but those changes to the landscape have not tempted VuFind away from basically the same set of components it began with when it first adopted Zend Framework, all of which continue to perform well.
