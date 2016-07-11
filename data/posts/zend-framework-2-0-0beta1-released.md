---
layout: post
title: Zend Framework 2.0.0beta1 Released!
date: 2011-10-18T15:00:00Z
update: 2011-10-18T15:00:00Z
author: Matthew Weier O'Phinney
url_author: http://mwop.net/
permalink: /blog/zend-framework-2-0-0beta1-released.html
categories:
- blog
- released

---

 The Zend Framework community is pleased to announce the immediate availability of Zend Framework 2.0.0beta1. Packages and installation instructions are available at:

  <http://packages.zendframework.com/>  This is the first in a series of planned beta releases. The beta release cycle will follow the "gmail" style of betas, whereby new features will be added in each new release, and BC will not be guaranteed; beta releases will happen _no less than_ every six weeks. The desire is for developers to adopt and work with new components as they are shipped, and provide feedback so we can polish the distribution.

 Once all code in the proposed [standard distribution](http://framework.zend.com/wiki/pages/viewpage.action?pageId=43745438) has reached maturity and reasonable stability, we will freeze the API and prepare for Release Candidate status.

 Featured components and functionality of 2.0.0beta1 include:

- New and refactored autoloaders: 
  - Zend\\Loader\\StandardAutoloader
  - Zend\\Loader\\ClassMapAutoloader
  - Zend\\Loader\\AutoloaderFactory
- New plugin broker strategy 
  - Zend\\Loader\\Broker and Zend\\Loader\\PluginBroker
- Reworked Exception system 
  - Allow catching by specific Exception type
  - Allow catching by component Exception type
  - Allow catching by SPL Exception type
  - Allow catching by base Exception type
- Rewritten Session component
- Refactored View component 
  - Split helpers into a PluginBroker
  - Split variables into a Variables container
  - Split script paths into a TemplateResolver
  - Renamed base View class "PhpRenderer"
  - Refactored helpers to utilize \_\_invoke() when possible
- Refactored HTTP component
- New Zend\\Cloud\\Infrastructure component
- New EventManager component
- New Dependency Injection (Zend\\Di) component
- New Code component 
  - Incorporates refactored versions of former Reflection and CodeGenerator components.
  - Introduces Scanner component.
  - Introduces annotation system.

 The above components provide a solid foundation for Zend Framework 2, and largely make up the framework "core". However, the cornerstone feature of beta1 is what they enable: the new MVC layer:

- Zend\\Module, for developing modular application architectures.
- Zend\\Mvc, a completely reworked MVC layer built on top of HTTP, EventManager, and Di.

 We've built a [skeleton application](http://github.com/zendframework/ZendSkeletonApplication) and a [skeleton module](http://github.com/zendframework/ZendSkeletonModule) to help get you started, as well as a [quick start guide to the MVC](http://packages.zendframework.com/docs/latest/manual/en/zend.mvc.quick-start.html); the new MVC is truly flexible, and moreover, simple and powerful.

 Additionally, for those who haven't clicked on the packages link above, we are debuting our new distribution mechanisms for ZF2: the ability to use [Pyrus](http://pear2.php.net) to install individual components and/or groups of components.

 Since mid-August, we've gone from a _few dozen_ pull requests on the [ZF2 git repository](http://github.com/zendframework/zf2) to **_over 500_**, originating from both long-time Zend Framework contributors as well as those brand-new to the project. I'd like to thank each and every one of them, but also call out several individuals who have made some outstanding and important contributions during that time frame:

- [Evan Coury](http://evan.pro), who prototyped and then implemented the new module system.
- [Rob Allen](http://akrabat.com), who, because he was doing a tutorial at [PHPNW](http://conference.phpnw.org.uk/) on ZF2, provided a lot of early feedback, ideas, and advice on the direction of the MVC.
- [Ben Scholzen](http://www.dasprids.de/), who wrote a new router system, in spite of a massive injury from a cycling accident.
- [Ralph Schindler](http://ralphschindler.com), who has had to put up with my daily "devil's advocate" and "think of the user!" rants for the past several months, and still managed to provide comprehensive code manipulation tools, a Dependency Injection framework, and major contributions to the HTTP component.
- [Enrico Zimuel](http://www.zimuel.it/en/), who got tossed requirements for the cloud infrastructure component, and then had to rework most of it after rewriting the HTTP client from the ground up... and who still managed three back-to-back-to-back conferences as we prepared the release.
- [Artur Bodera](http://www.linkedin.com/in/abodera), who often has played devil's advocate, and persisted pressing his opinions on the direction of the framework, often despite heavy opposition. We may not implement all (or many) of the features you want, but you've definitely influenced the direction of the MVC incredibly.
- [Pádraic Brady](http://blog.astrumfutura.com/), who [started the runaway train rolling with a rant](http://zend-framework-community.634137.n4.nabble.com/A-Rant-From-Mr-Grumpy-on-ZF2-tp3721463p3721463.html), and helped make the project much more transparent, enabling the MVC development to occur in the first place.

 Welcome to the ZF2 beta cycle!
