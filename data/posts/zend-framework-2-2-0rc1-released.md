---
layout: post
title: Zend Framework 2.2.0rc1 Released!
date: 2013-05-02T00:50:00Z
update: 2013-05-02T00:50:00Z
author: Matthew Weier O'Phinney
url_author: http://mwop.net/
permalink: /blog/zend-framework-2-2-0rc1-released.html
categories:
- blog
- released

---

 The Zend Framework community is pleased to announce the immediate availability of Zend Framework 2.2.0rc1! Packages and installation instructions are available at:

- [http://packages.zendframework.com/](https://packages.zendframework.com/)

 This is a _release candidate_. It is not the final release, and while stability is generally considered good, there may still be issues to resolve between now and the stable release. Use in production with caution.

 **DO** please test your applications on this RC, as we would like to ensure that it remains backwards compatible, and that the migration path is smooth.

Changes in this version
-----------------------

- **Addition of many more plugin managers and abstract service factories.** In order to simplify usage of the `ServiceManager` as an [Inversion of Control](http://en.wikipedia.org/wiki/Inversion_of_Control) container, as well as to provide more flexibility in and consistency in how various framework components are consumed, a number of plugin managers and service factories were created and enabled.

 Among the various plugin managers created are Translator loader manager, a Hydrator plugin manager (allowing named hydrator instances), and an InputFilter manager.

 New factories include a Translator service factory, and factories for both the Session configuration and SessionManager.

 New abstract factories include one for the DB component (allowing you to manage multiple named adapters), Loggers (for having multiple Logger instances), Cache storage (for managing multiple cache backends), and Forms (which makes use of the existing FormElementsPluginManager, as well as the new Hydrator and InputFilter plugin managers).
- **Data Definition Language (DDL) support in Zend\\Db.** DDL provides the ability to create, alter, and drop tables in a relational database system. Zend\\Db now offers abstraction around DDL, and specifically MySQL and ANSI SQL-92; we will gradually add this capability for the other database vendors we support.
- **Authentication:** The DB adapter now supports non-RDBMS credential validation.
- **Cache:** New storage backend: Redis.
- **Code:** The ClassGenerator now has a removeMethod() method.
- **Console:** Incremental improvements to layout and colorization of banners and usage messages; fixes for how literal and non-literal matches are returned.
- **DB:** New DDL support (noted earlier); many incremental improvements.
- **Filter:** New DateTimeFormatter filter.
- **Form:** Many incremental improvements to selected elements; new FormAbstractServiceFactory for defining form services; minor improvements to make the form component work with the DI service factory.
- **InputFilter**: new CollectionInputFilter for working with form Collections; new InputFilterPluginManager providing integration and services for the ServiceManager.
- **I18n:** We removed ext/intl as a hard requirement, and made it only a suggested requirement; the Translator has an optional dependency on the EventManager, providing the ability to tie into "missing message" and "missing translations" events; new country-specific PhoneNumber validator.
- **ModuleManager:** Now allows passing actual Module instances (not just names).
- **Navigation:** Incremental improvements, particularly to URL generation.
- **MVC:** You can now configure the initial set of MVC event listeners in the configuration file; the MVC stack now detects generic HTTP responses when detecting event short circuiting; the default ExceptionStrategy now allows returning JSON; opt-in translatable segment routing; many incremental improvements to the AbstractRestfulController to make it more configurable and extensible; the Forward plugin was refactored to no longer require a ServiceLocatorAware controller, and instead receive the ControllerManager via its factory.
- **Paginator:** Support for TableGateway objects.
- **ServiceManager:** Incremental improvements; performance optimizations; delegate factories, which provide a way to write factories for objects that replace a service with a decorator; "lazy" factories, allowing the ability to delay factory creation invocation until the moment of first use.
- **Stdlib:** Addition of a HydratorAwareInterface; creation of a HydratorPluginManager.
- **SOAP:** Major refactor of WSDL generation to make it more maintainable.
- **Validator:** New Brazilian IBAN format for IBAN validator; validators now only return unique error messages; improved Maestro detection in CreditCard validator.
- **Version:** use the ZF website API for finding the latest version, instead of GitHub.
- **View:** Many incremental improvements, primarily to helpers; deprecation of the Placeholder Registry and removal of it from the implemented placeholder system; new explicit factory classes for helpers that have collaborators (making them easier to override/replace).

Changelog
---------

 Almost 200 patches were applied for 2.2.0. We will not release a full changelog until we create the stable release. In the meantime, you can view a full set of patches applied for 2.2.0 in the 2.2.0 milestone on GitHub:

- [Zend Framework 2.2.0 milestone](https://github.com/zendframework/zf2/issues?milestone=14&state=closed)

Other Announcements
-------------------

 Around a month ago, we migrated [Zend Framework 1 to GitHub](https://github.com/zendframework/zf1). At that time, we also migrated active issues created since 1.12.0 to the [GitHub issue tracker](https://github.com/zendframework/zf1/issues), and marked our self-hosted issue tracker read-only. We have decided to turn off that issue tracker, but still retain the original issues at their original locations for purposes of history and transparency. You can find information on the change on our [ issues landing page](/issues).

Thank You!
----------

 Please join me in thanking everyone who provided new features and code improvements for this upcoming 2.2.0 release!

Roadmap
-------

 We plan to release additional RCs every 3-5 days until we feel the 2.2.0 release is generally stable; we anticipate a stable release in the next 2-3 weeks.

 During the RC period, we will be expanding on documentation, and fixing any critical issues brought to our attention.

 Again, **DO** please test your applications on this RC, as we would like to ensure that it remains backwards compatible, and that the migration path is smooth.
