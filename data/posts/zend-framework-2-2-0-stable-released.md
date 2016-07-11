---
layout: post
title: Zend Framework 2.2.0 Stable Released!
date: 2013-05-15T17:00:00Z
update: 2013-05-15T17:00:00Z
author: Matthew Weier O'Phinney
url_author: http://mwop.net/
permalink: /blog/zend-framework-2-2-0-stable-released.html
categories:
- blog
- released

---

 The Zend Framework community is pleased to announce the immediate availability of Zend Framework 2.2.0! Packages and installation instructions are available at:

- [http://framework.zend.com/downloads/latest](/downloads/latest)

 This is the first _stable_ release in the 2.2 series.

Usability and Consistency
-------------------------

 The primary focus of the 2.2 release has been usability and consistency, primarily with regard to creation and configuration of services such as hydrators, input filters, logs, DB connections, cache objects, translators, and forms.

 Most of these services now have what are known as "Abstract Factories" that are either registered by default, or can be added quickly to your application configuration. Abstract factories are used by the service manager when you have multiple services that follow the same instantiation pattern, but which have different names. The typical pattern the new abstract factories follow is to use key/configuration pairs under a common top-level configuration key to describe the instances desired:

 
    <pre class="highlight"><span style="color: #000000">
    <span style="color: #0000BB"><?php<br></br></span><span style="color: #DD0000">'log' </span><span style="color: #007700">=> array(<br></br>    </span><span style="color: #DD0000">'Application\Log' </span><span style="color: #007700">=> array(<br></br>        </span><span style="color: #DD0000">'writers' </span><span style="color: #007700">=> array(<br></br>            array(<br></br>                </span><span style="color: #DD0000">'name'     </span><span style="color: #007700">=> </span><span style="color: #DD0000">'stream'</span><span style="color: #007700">,<br></br>                </span><span style="color: #DD0000">'priority' </span><span style="color: #007700">=> </span><span style="color: #0000BB">1000</span><span style="color: #007700">,<br></br>                </span><span style="color: #DD0000">'options'  </span><span style="color: #007700">=> array(<br></br>                    </span><span style="color: #DD0000">'stream' </span><span style="color: #007700">=> </span><span style="color: #DD0000">'data/logs/app.log'</span><span style="color: #007700">,<br></br>                ),<br></br>            ),<br></br>        ),<br></br>    ),<br></br>),<br></br></span>
    </span>


 The above creates a logger named "Application\\Log" which you can retrieve directly from the service manager. If you wanted to have additional loggers, you could do so by adding additional entries under the "log" heading, each named, and each providing configuration for a logger.

 Besides the logger abstract factory illustrated above, the following components each have abstract factories now, too, using the configuration keys noted:

- `Zend\Cache`: "caches" configuration section, allowing multiple named cache storage objects.
- `Zend\Db`: "adapters" subkey of the "db" configuration section; this abstract factory allows you to finally have multiple named DB adapter instances, effectively allowing for read-only and write-only connections.
- `Zend\Form`: "forms" configuration section (which makes use of several old and new plugin managers, as noted below).

 A number of new plugin managers were also added. Plugin managers are specialized service manager instances used by objects that will be consuming many different related object instances, often based on runtime conditions. As examples, view helpers and controller plugins are mediated by plugin managers.

 The new plugin manager instances include:

- `Zend\Stdlib\Hydrator\HydratorPluginManager`, for retrieving hydrator instances. This allows re-use of individual hydrators, and coupled with the forms abstract factory, allows usage of custom hydrators across your form instances.
- `Zend\InputFilter\InputFilterPluginManager`, for retrieving (configurable) input filter instances. This allows re-use of input filters, as well as ensures that all input instances are provided with custom validators and/or filters (from the existing validator and filter plugin managers). The forms abstract factory makes use of this, which allows us to finally tie together the various plugin managers to create fully configurable and custom forms.

 Finally, a couple new service factories were created. Service factories usually have a 1:1 relationship between the named service and the instance provided, and are ideal for situations where you only need one instance of a given service type. In the case of the new factories for 2.2, these include _translators_ and _sessions_.

Data Definition Language Abstraction
------------------------------------

 Zend Framework 2.2 also offers initial support in `Zend\Db` for dynamic DDL queries. DDL, for Data Definition Language, is a subset of SQL that comprises different commands for building RDBMS data structures like tables, columns, constraints, indexes, views, triggers and the like.

 Initial support is limited to creating tables with SQL92 data-types, and some specialization for MySQL support. Here is an example of     CREATE 
        TABLE
 statement:

 
    <pre class="highlight"><span style="color: #000000">
    <span style="color: #0000BB"><?php<br></br>    </span><span style="color: #007700">use </span><span style="color: #0000BB">Zend</span><span style="color: #007700">\</span><span style="color: #0000BB">Db</span><span style="color: #007700">\</span><span style="color: #0000BB">Sql</span><span style="color: #007700">\</span><span style="color: #0000BB">Sql</span><span style="color: #007700">;<br></br>    use </span><span style="color: #0000BB">Zend</span><span style="color: #007700">\</span><span style="color: #0000BB">Db</span><span style="color: #007700">\</span><span style="color: #0000BB">Sql</span><span style="color: #007700">\</span><span style="color: #0000BB">Ddl</span><span style="color: #007700">;<br></br><br></br>    </span><span style="color: #0000BB">$t </span><span style="color: #007700">= new </span><span style="color: #0000BB">Ddl</span><span style="color: #007700">\</span><span style="color: #0000BB">CreateTable</span><span style="color: #007700">();<br></br>    </span><span style="color: #0000BB">$t</span><span style="color: #007700">-></span><span style="color: #0000BB">setTable</span><span style="color: #007700">(</span><span style="color: #DD0000">'bar'</span><span style="color: #007700">);<br></br>    </span><span style="color: #0000BB">$t</span><span style="color: #007700">-></span><span style="color: #0000BB">addColumn</span><span style="color: #007700">(new </span><span style="color: #0000BB">Ddl</span><span style="color: #007700">\</span><span style="color: #0000BB">Column</span><span style="color: #007700">\</span><span style="color: #0000BB">Integer</span><span style="color: #007700">(<br></br>        </span><span style="color: #DD0000">'id'</span><span style="color: #007700">, <br></br>        </span><span style="color: #0000BB">12</span><span style="color: #007700">, <br></br>        </span><span style="color: #0000BB">true</span><span style="color: #007700">, <br></br>        </span><span style="color: #0000BB">null</span><span style="color: #007700">,<br></br>        [</span><span style="color: #DD0000">'auto_increment' </span><span style="color: #007700">=> </span><span style="color: #0000BB">true</span><span style="color: #007700">, </span><span style="color: #DD0000">'comment' </span><span style="color: #007700">=> </span><span style="color: #DD0000">'Some comment'</span><span style="color: #007700">]<br></br>    ));<br></br>    </span><span style="color: #0000BB">$t</span><span style="color: #007700">-></span><span style="color: #0000BB">addColumn</span><span style="color: #007700">(new </span><span style="color: #0000BB">Ddl</span><span style="color: #007700">\</span><span style="color: #0000BB">Column</span><span style="color: #007700">\</span><span style="color: #0000BB">Varchar</span><span style="color: #007700">(</span><span style="color: #DD0000">'name'</span><span style="color: #007700">, </span><span style="color: #0000BB">255</span><span style="color: #007700">));<br></br>    </span><span style="color: #0000BB">$t</span><span style="color: #007700">-></span><span style="color: #0000BB">addColumn</span><span style="color: #007700">(new </span><span style="color: #0000BB">Ddl</span><span style="color: #007700">\</span><span style="color: #0000BB">Column</span><span style="color: #007700">\</span><span style="color: #0000BB">Char</span><span style="color: #007700">(</span><span style="color: #DD0000">'foo'</span><span style="color: #007700">, </span><span style="color: #0000BB">20</span><span style="color: #007700">));<br></br>    </span><span style="color: #0000BB">$t</span><span style="color: #007700">-></span><span style="color: #0000BB">addConstraint</span><span style="color: #007700">(new </span><span style="color: #0000BB">Ddl</span><span style="color: #007700">\</span><span style="color: #0000BB">Constraint</span><span style="color: #007700">\</span><span style="color: #0000BB">PrimaryKey</span><span style="color: #007700">(</span><span style="color: #DD0000">'id'</span><span style="color: #007700">));<br></br>    </span><span style="color: #0000BB">$t</span><span style="color: #007700">-></span><span style="color: #0000BB">addConstraint</span><span style="color: #007700">(new </span><span style="color: #0000BB">Ddl</span><span style="color: #007700">\</span><span style="color: #0000BB">Constraint</span><span style="color: #007700">\</span><span style="color: #0000BB">UniqueKey</span><span style="color: #007700">(<br></br>        [</span><span style="color: #DD0000">'name'</span><span style="color: #007700">, </span><span style="color: #DD0000">'foo'</span><span style="color: #007700">],<br></br>        </span><span style="color: #DD0000">'my_unique_key'<br></br>    </span><span style="color: #007700">));<br></br><br></br>    </span><span style="color: #0000BB">$sql </span><span style="color: #007700">= new </span><span style="color: #0000BB">Sql</span><span style="color: #007700">(</span><span style="color: #0000BB">$adapter</span><span style="color: #007700">);<br></br>    echo </span><span style="color: #0000BB">$sql</span><span style="color: #007700">-></span><span style="color: #0000BB">getSqlStringForSqlObject</span><span style="color: #007700">(</span><span style="color: #0000BB">$t</span><span style="color: #007700">);<br></br></span>
    </span>


Once this table is created, it can then be altered:

 
    <pre class="highlight"><span style="color: #000000">
    <span style="color: #0000BB"><?php<br></br>    $t </span><span style="color: #007700">= new </span><span style="color: #0000BB">Ddl</span><span style="color: #007700">\</span><span style="color: #0000BB">AlterTable</span><span style="color: #007700">(</span><span style="color: #DD0000">'bar'</span><span style="color: #007700">);<br></br>    </span><span style="color: #0000BB">$t</span><span style="color: #007700">-></span><span style="color: #0000BB">changeColumn</span><span style="color: #007700">(</span><span style="color: #DD0000">'name'</span><span style="color: #007700">, new </span><span style="color: #0000BB">Ddl</span><span style="color: #007700">\</span><span style="color: #0000BB">Column</span><span style="color: #007700">\</span><span style="color: #0000BB">Varchar</span><span style="color: #007700">(</span><span style="color: #DD0000">'new_name'</span><span style="color: #007700">, </span><span style="color: #0000BB">50</span><span style="color: #007700">));<br></br>    </span><span style="color: #0000BB">$t</span><span style="color: #007700">-></span><span style="color: #0000BB">addColumn</span><span style="color: #007700">(new </span><span style="color: #0000BB">Ddl</span><span style="color: #007700">\</span><span style="color: #0000BB">Column</span><span style="color: #007700">\</span><span style="color: #0000BB">Varchar</span><span style="color: #007700">(</span><span style="color: #DD0000">'another'</span><span style="color: #007700">, </span><span style="color: #0000BB">255</span><span style="color: #007700">));<br></br>    </span><span style="color: #0000BB">$t</span><span style="color: #007700">-></span><span style="color: #0000BB">addColumn</span><span style="color: #007700">(new </span><span style="color: #0000BB">Ddl</span><span style="color: #007700">\</span><span style="color: #0000BB">Column</span><span style="color: #007700">\</span><span style="color: #0000BB">Varchar</span><span style="color: #007700">(</span><span style="color: #DD0000">'other_id'</span><span style="color: #007700">, </span><span style="color: #0000BB">255</span><span style="color: #007700">));<br></br>    </span><span style="color: #0000BB">$t</span><span style="color: #007700">-></span><span style="color: #0000BB">dropColumn</span><span style="color: #007700">(</span><span style="color: #DD0000">'foo'</span><span style="color: #007700">);<br></br>    </span><span style="color: #0000BB">$t</span><span style="color: #007700">-></span><span style="color: #0000BB">addConstraint</span><span style="color: #007700">(new </span><span style="color: #0000BB">Ddl</span><span style="color: #007700">\</span><span style="color: #0000BB">Constraint</span><span style="color: #007700">\</span><span style="color: #0000BB">ForeignKey</span><span style="color: #007700">(<br></br>        </span><span style="color: #DD0000">'my_fk'</span><span style="color: #007700">, </span><span style="color: #DD0000">'other_id'</span><span style="color: #007700">, </span><span style="color: #DD0000">'other_table'</span><span style="color: #007700">, </span><span style="color: #DD0000">'id'</span><span style="color: #007700">, </span><span style="color: #DD0000">'CASCADE'</span><span style="color: #007700">, </span><span style="color: #DD0000">'CASCADE'<br></br>    </span><span style="color: #007700">));<br></br>    </span><span style="color: #0000BB">$t</span><span style="color: #007700">-></span><span style="color: #0000BB">dropConstraint</span><span style="color: #007700">(</span><span style="color: #DD0000">'my_index'</span><span style="color: #007700">);<br></br>    echo </span><span style="color: #0000BB">$sql</span><span style="color: #007700">-></span><span style="color: #0000BB">getSqlStringForSqlObject</span><span style="color: #007700">(</span><span style="color: #0000BB">$t</span><span style="color: #007700">);<br></br></span>
    </span>


Or even dropped:

 
    <pre class="highlight"><span style="color: #000000">
    <span style="color: #0000BB"><?php<br></br>    $dt </span><span style="color: #007700">= new </span><span style="color: #0000BB">Ddl</span><span style="color: #007700">\</span><span style="color: #0000BB">DropTable</span><span style="color: #007700">(</span><span style="color: #DD0000">'bar'</span><span style="color: #007700">);<br></br>    echo </span><span style="color: #0000BB">$sql</span><span style="color: #007700">-></span><span style="color: #0000BB">getSqlStringForSqlObject</span><span style="color: #007700">(</span><span style="color: #0000BB">$dt</span><span style="color: #007700">);<br></br></span>
    </span>


 What can this be used for?

 That is where you come in. This particular feature was asked for numerous times during ZF1 development. We'd like to see what kind of ZF2 modules can be created with this base infrastructure. Migration assistant? ORM database creation tool? Advanced CMS? Let us know; we'll be adding more vendor specific support over the 2.2 to 2.3 timeline.

New Service Wrappers
--------------------

 Zend Framework has a long history of providing API wrappers; in fact, they were a prominent part of the initial pre-release! The tradition continues in ZF2, though each API wrapper now has its own repository.

 Alongside the 2.2.0 release, we're also providing initial beta releases of two new service components: `ZendService_Api` and `ZendService_OpenStack`.

### ZendService\_Api

 This is an HTTP microframework for consuming generic API calls in PHP. This framework can be used to create PHP libraries that consume specific HTTP APIs using either a simple configuration array or files. This project uses the `Zend\Http\Client` component of Zend Framework 2. [Enrico has blogged about the component previously.](http://www.zimuel.it/en/zendservice-api-micro-http-framework/)

### ZendService\_OpenStack

 We began the development of a new library to support the last API version of [OpenStack](http://www.openstack.org). The goal of this component is to simplify the usage of OpenStack in PHP, providing a simple object oriented interface to its API services. This component is based on `ZendService_Api`, giving us a flexible way to update the HTTP specification with the future API versions.

ZFTool Diagnostic Features
--------------------------

 Artur Bodera (aka Thinkscape) provided a new diagnostics feature for ZFTool. Using this feature, we can allow the execution of customized diagnostics tests in ZF2 projects, including testing for the required PHP version, testing for specific PHP extensions, testing for specific ZF2 modules, testing for specific PHP INI settings, and more; [read the documentation to get an idea of the variety of tests available.](https://github.com/zendframework/ZFTool/blob/master/docs/DIAGNOSTICS.md)

 Moreover, with the collaboration of the [LiipMonitor project](https://github.com/liip/LiipMonitor), we decided to create common interfaces for performing diagnostic tests in PHP applications. An initial draft is available in the [ZendDiagnostic repository](https://github.com/zendframework/ZendDiagnostics).

 The diagnostics feature is available in the [ latest version of ZFTool](https://packages.zendframework.com/zftool.phar).

Hydrator Improvements
---------------------

 As noted earlier, `Zend\Stdlib\Hydrator` now has a plugin manager you can compose into your objects for managing hydrator instances. However, beyond that, we also now have an "Aggregate Hydrator", which allows you to provide specialized mapping of your object types to hydrators via an event-based system.

 Why is this exciting? Many of our users utilize [Doctrine](http://doctrine-project.org) as an Object Relational Mapping (ORM) system. Oftentimes, the entities that you work with will also form a hierarchical structure. The Aggregate Hydrator allows allows you to attach a single hydrator to the parent object, and ensure that all child and descendant objects are either hydrated or extracted according to their type.

Reducing Dependencies
---------------------

 We have started work on a new story for the framework: reducing dependencies for individual components. We have received feedback from a number of developers and organizations indicating that even though each component can be installed individually, the number of dependencies most components mark as required leads to a situation where they feel they must choose whether or not they adopt the framework, versus adopting just the component. While of course we'd like them to adopt the framework, we'd rather they get a taste for it, if you will.

 While this story is primarily slated for 2.3, we have made our first steps in 2.2, with the `Zend\Feed` and `Zend\Validator` components.

`Zend\Validator` removed its dependency on the i18n component. We achieved this by creating [Separated Interfaces](http://martinfowler.com/eaaCatalog/separatedInterface.html) for the translator. Considering translation was only enabled if you explicitly injected a translator, this was a natural course of action. (It also introduced a minor backwards compatibility break; see below for more information.)

 For `Zend\Feed`, many "required" dependencies were actually optional already, and we could mark them as such. There were two that were not, however, and which required similar treatment as `Zend\Validator` in creating separated interfaces: the service manager (used for extension management) and HTTP (for fetching remote feeds with the reader). Interfaces were developed for each of these, and `Zend\Feed` now has only two required dependencies. A nice side benefit is that you can now use third-party HTTP clients with `Zend\Feed\Reader`!

Migration Notes
---------------

 While we have worked hard to keep code backwards compatible (BC), there are a few noteworth changes that _may_ affect your code.

- `Zend\Validator` no longer directly consumes a `Zend\I18n\Translator\Translator` instance; instead, you must either implement `Zend\Validator\Translator\TranslatorInterface` or use `Zend\Mvc\I18n\Translator`. In most cases, this change should be transparent, as validator instances managed by the ValidatorPluginManager will already be using the correct instance.
- In 2.1.5, a BC break was accidently introduced into `Zend\Navigation` in order to enable a feature: MVC pages were altered to always use route match values when available when generating URIs. 2.2.0 was modified to add a flag to enable this behavior on demand, but defaults to the original behavior, which does not pass the route match values to the pages. If you relied on this behavior in 2.1.5, add the following option to your individual MVC page definitions: 
    <pre class="highlight"><span style="color: #000000">
    <span style="color: #0000BB"><?php<br></br></span><span style="color: #DD0000">'use_route_match' </span><span style="color: #007700">=> </span><span style="color: #0000BB">true</span><span style="color: #007700">,<br></br></span>
    </span>

Other Notable Improvements
--------------------------

- **Authentication:** The DB adapter now supports non-RDBMS credential validation.
- **Cache:** New storage backend: Redis.
- **Code:** The ClassGenerator now has a removeMethod() method.
- **Console:** Incremental improvements to layout and colorization of banners and usage messages; fixes for how literal and non-literal matches are returned.
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

 Greater than 150 patches were applied for 2.2.0.

- [http://framework.zend.com/changelog/2.2.0](/changelog/2.2.0)

Other Announcements
-------------------

 Over a month ago, we migrated [Zend Framework 1 to GitHub](https://github.com/zendframework/zf1). At that time, we also migrated active issues created since 1.12.0 to the [GitHub issue tracker](https://github.com/zendframework/zf1/issues), and marked our self-hosted issue tracker read-only. We have decided to turn off that issue tracker, but still retain the original issues at their original locations for purposes of history and transparency. You can find information on the change on our [ issues landing page](/issues).

Thank You!
----------

 Please join me in thanking everyone who provided new features and code improvements for the 2.2.0 release! We had a huge leap forward in usability of many components, and a number of key new features that make developing applications simpler. We'll be continuing on these themes for the next release as well.

Roadmap
-------

 Maintenance releases are scheduled for the third Wednesday of each month; expect 2.2.1 on 19 June 2013. Minor releases are scheduled roughly every quarter; look for 2.3 sometime around mid-August or early September. Proposals and ideas for stories will be presented on the zf-contributors mailing list; subscribe by sending an email to zf-contributors-subscribe [at] lists.zend.com if you are interested in assisting with its development.
