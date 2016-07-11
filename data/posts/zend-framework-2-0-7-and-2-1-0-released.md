---
layout: post
title: Zend Framework 2.0.7 and 2.1.0 Released!
date: 2013-01-30T21:15:00Z
update: 2013-01-30T23:55:00Z
author: Matthew Weier O'Phinney
url_author: http://mwop.net/
permalink: /blog/zend-framework-2-0-7-and-2-1-0-released.html
categories:
- blog
- released

---

 The Zend Framework community is pleased to announce the immediate availability of _both_ Zend Framework **2.0.7** and **2.1.0**! Packages and installation instructions are available at:

- [http://framework.zend.com/downloads/latest](/downloads/latest)

 All existing users of ZF 2.0.x are encouraged to upgrade to 2.1.0; if you prefer to only obtain fixes without new features, you may upgrade to 2.0.7.

2.0.7
-----

 This is the last scheduled release in the 2.0 series, and contains over 150 bugfixes. [Read the changelog for the full set of improvements](/changelog/2.0.7).

 Many thanks to all the contributors who helped polish this initial feature branch and improve it!

2.1.0
-----

 **2.1.0** is the first new feature release for ZF2. Most features are incremental improvements on existing components; we also have one brand new component as well. Things you'll see include:

- New **Zend\\Permissions\\Rbac** component, providing Role-Based Authorization Controls. These complement our existing `Zend\Permissions\Acl` component, providing another mechanism for providing authorization for your applications. We have Kyle Spraggs to thank for this addition.
- New **Zend\\Test** component, providing the ability to perform functional or integration testing on your ZF2 applications, courtesy of Blanchon Vincent.
- Support for **Oracle** and **IBM DB2** databases in `Zend\Db`. Many thanks to Ralph Schindler for spearheading these efforts.
- A new **Zend\\Stdlib\\StringUtils** class to provide unified functionality around manipulating strings, particularly those in multibyte character sets. Thanks to Marc Bennewitz!
- **scrypt** support for `Zend\Crypt`. Thanks go to Enrico Zimuel for this addition.
- Apache **htpassword** support in `Zend\Crypt` and in the **HTTP** authentication adapter; thanks go to Enrico Zimuel again!
- New integration for handling and manipulating file uploads with the `InputFilter`, `Form`, and `Mvc` components, including capablities around the [PRG](http://en.wikipedia.org/wiki/Post/Redirect/Get) pattern. Please thank Chris Martin for his huge amount of work around this!
- A new **render.error** event, allowing you to fail gracefully in the event of a view rendering error. This allows you to present a static error page in such situations, as well as to log the problem. Thanks go to radnan for this addition.
- Additional integration between a variety of plugin managers and the service manager was created, covering form elements, filters, validators, route classes, and serializers; this allows application-level configuration of these plugin managers, providing a simplified interface for configuring custom plugins.
- Martin Meredith provided seven new **traits** for end-user use in PHP 5.4 applications.
- The **Authentication** component received support for **storage chains** and **validators**.
- Better **console** support, including better help messages, increased capabilities around colorisation, and more.
- Many incremental improvements in `Zend\Db`; in particular, addition of **profiling** support, **cross-table select join** support, **derived table in select join**, and **literal** objects.
- **Zend\\Logger** has new **FirePHP**, **ChromePHP**, **MongoDB**, and **FingersCrossed** writers; thanks go to Walter Tamboer, Jeremy Mikola, and Stefan Kleff.
- The **MVC** layer sports more flexibility and capabilities in the AbstractRestfulController, including automated content-negotiation for JSON requests, and support for most HTTP methods, including OPTIONS and HEAD (and the ability to support arbitrary HTTP methods).
- **Zend\\Session** now has a **MongoDB** save handler, and provides **better interoperability** between sessions managed by ZF2 and 3rd party code.

 For the complete list of more than 140 changes, [read the changelog](/changelog/2.1.0).

New Components
--------------

 In addition to the new components in 2.1.0, we have two new service components to announce:

- [ZendService\_Apple\_Apns](https://github.com/zendframework/ZendService_Apple_Apns), which provides push notification capabilities for Apple iOS. This component may be installed via Composer or Pyrus.
- [ZendService\_Google\_Gcm](https://github.com/zendframework/ZendService_Google_Gcm), which provides push notification capabilities for Google Android. This component may be installed via Composer or Pyrus.

 I'd like to thank Mike Willbanks for the time and effort he put into these components, and for providing them to the project!

New Tooling
-----------

 Enrico Zimuel has been hacking on the **ZFTool** project, to provide tooling support for the framework. This has resulted in [zftool.phar](https://packages.zendframework.com/zftool.phar), which provides the following capabilities at this time:

- Skeleton application creation
- Module creation within a skeleton
- Autoloader classmap creation
- ZF2 installation to a directory

 Expect more capabilities in the future!

New Responsive Website
----------------------

 Regular visitors to the website may notice some changes. Contributor Frank Brückner has been updating the site to implement a responsive design, provide more consistency between pages, and in particular, navigation, and over all make the site cleaner.

 The end result: the site should now be usable on a variety of platforms and screen sizes, allowing you to visit on your desktop, tablet, or mobile phone!

Potential Breakage
------------------

 Both 2.0.7 and 2.1.0 include a fix to the classes `Zend\Filter\Encrypt` and `Zend\Filter\Decrypt` which may pose a small break for end-users. Each requires an encryption key be passed to either the constructor or the `setKey()` method now; this was done to improve the security of each class.

 In 2.1.0, `Zend\Session` includes a new `Zend\Session\Storage\SessionArrayStorage` class, which acts as a direct proxy to the `$_SESSION` superglobal. The `SessionManager` class now uses this new storage class by default, in order to fix an error that occurs when directly manipulating nested arrays of `$_SESSION` in third-party code. For most users, the change will be seamless. Those affected will be those (a) directly accessing the storage instance, and (b) using object notation to access session members:

 
    <pre class="highlight"><span style="color: #000000">
    <span style="color: #0000BB"><?php<br></br>$foo </span><span style="color: #007700">= </span><span style="color: #0000BB">null</span><span style="color: #007700">;<br></br></span><span style="color: #FF8000">/** @var $storage Zend\Session\Storage\SessionStorage */<br></br></span><span style="color: #007700">if (isset(</span><span style="color: #0000BB">$storage</span><span style="color: #007700">-></span><span style="color: #0000BB">foo</span><span style="color: #007700">)) {<br></br>    </span><span style="color: #0000BB">$foo </span><span style="color: #007700">= </span><span style="color: #0000BB">$storage</span><span style="color: #007700">-></span><span style="color: #0000BB">foo</span><span style="color: #007700">;<br></br>}<br></br></span>
    </span>


 If you are using array notation, as in the following example, your code remains forwards compatible:

 
    <pre class="highlight"><span style="color: #000000">
    <span style="color: #0000BB"><?php<br></br>$foo </span><span style="color: #007700">= </span><span style="color: #0000BB">null</span><span style="color: #007700">;<br></br><br></br></span><span style="color: #FF8000">/** @var $storage Zend\Session\Storage\SessionStorage */<br></br></span><span style="color: #007700">if (isset(</span><span style="color: #0000BB">$storage</span><span style="color: #007700">[</span><span style="color: #DD0000">'foo'</span><span style="color: #007700">])) {<br></br>    </span><span style="color: #0000BB">$foo </span><span style="color: #007700">= </span><span style="color: #0000BB">$storage</span><span style="color: #007700">[</span><span style="color: #DD0000">'foo'</span><span style="color: #007700">];<br></br>}<br></br></span>
    </span>


 If you are not working directly with the storage instance, you will be unaffected.

 For those affected, the following courses of action are possible:

- Update your code to replace object property notation with array notation, OR
- Initialize and register a `Zend\Session\Storage\SessionStorage` object explicitly with the session manager instance.

Thank You!
----------

 As always, I'd like to thank the many contributors who made these releases possible! The project is gaining in consistency and capabilities daily as a result of your efforts.

Roadmap
-------

 Maintenance releases happen monthly on the third Wednesday; expect version 2.1.1 on 20 February 2013. The next minor release, 2.2.0, is tentatively scheduled for 24 April 2013.
