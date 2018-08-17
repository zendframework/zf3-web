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

VuFind was originally written as a stand-alone model-view-controller application using a homegrown framework, but it was completely rewritten during 2011 and 2012 to use Zend Framework, in order to improve extensibility and better follow community standards.

## Requirements

VuFind had several key requirements that had to be taken into account when selecting a framework:

  * **Extensibility**: VuFind is widely adopted and covers a broad range of use cases. Users need the ability to customize and override nearly every part of the software. Ideally, customizations should be achieved by adding custom subclasses without having to modify any core code. The *zend-mvc* and *zend-servicemanager* components provide a perfect mechanism for customization, since all core logic can be built as overrideable services, and plugin managers can be used to create configurable extensions. The *zend-modulemanager* provides a way to encapsulate custom code and manage it separately from the VuFind core.
  * **Custom Themes**: Just as users may need to override nearly any piece of code, they also need to override individual view templates. With *zend-view*, VuFind is able to provide simple PHP templates that, with the help of some VuFind-specific listeners and view helpers, can be organized into configurable themes that inherit from each other, allowing a user to override only the files that need to be changed.
  * **Multitenancy**: Some institutions use a single installation of VuFind to manage multiple independently customized sites. By using environment variables to control which modules and configurations are loaded, the extensibility and theming features listed above, combined with a bit of clever Apache configuration, enable multitenancy.
  * **Internationalization**: VuFind is used all over the world, so a translation layer is a necessity; *zend-i18n* meets this need well, allowing custom language file loaders and text-domain-based organization to simplify the management of translations.
  * **Command Line Utilities**: While VuFind is primarily a web application, it also uses a variety of command-line tools to perform maintenance tasks such as indexing records and managing Solr. The *zend-console* library allows command-line tools to be built that share common logic with the web application.
  * **Code Generation**: With its many customization options, automatic code/configuration generators can save users a great deal of time. VuFind's generators are powered by *zend-code*.
  * **Database Abstraction**: VuFind is designed to work with multiple relational database backends (primarily for storing user-generated content). The *zend-db* module provides a flexible and powerful mechanism to interact with a database when a full ORM like Doctrine is not necessary.
  * **Robust HTTP Interactions**: Because VuFind connects to a wide variety of APIs, it needs to make many different HTTP requests, originating from a variety of different server and network environments; *zend-http* offers the flexibility needed to make this work.
  * **Logging**: VuFind administrators and developers need logging to debug code in development and to monitor systems in production. The *zend-log* module allows a great deal of flexibility to meet these varied use cases.
  * **...and the rest**: VuFind also leverages a variety of Zend Framework components for other aspects of its functionality: *zend-config* for configuration management, *zend-feed* for RSS feed generation, *zend-mail* for email and text message sending, and *zend-paginator* for result pagination, among many others.
