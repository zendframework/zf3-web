---
layout: post
title: 2011-09-12 Dev status update
date: 2011-09-12 22:20
update: 2011-09-12 22:20
author: Matthew Weier O'Phinney
url_author: http://mwop.net/
permalink: /blog/2011-09-12-dev-status-update.html
categories:
- blog
- dev

---

 Zend Framework status update for the weeks of 30 August - 12 September 2011.

 Much has happened since our last update.

2011-08-31 IRC Meeting
----------------------

 First, we held our [ second IRC meeting](http://framework.zend.com/wiki/display/ZFDEV2/2011-08-31+Meeting+Log) on Wednesday, 31 August 2011. The intended purpose of the meeting was to discuss and vote on two RFCs, [Modules](http://framework.zend.com/wiki/display/ZFDEV2/RFC+-+ZF2+Modules) and [Distribution](http://framework.zend.com/wiki/pages/viewpage.action?pageId=43745438). Discussion was heated for much of the meeting, but a number of ideas were clarified and ratified during the process. In particular, we gained consensus surrounding the difference between components and modules, and started a conversation surrounding what we may include in modules in the future. Visit the [meeting log](http://framework.zend.com/wiki/display/ZFDEV2/2011-08-31+Meeting+Log) for more details.

MVC Prototyping
---------------

 Following the meeting, I created and published the first "official" MVC prototype in a [branch of my repository](https://github.com/weierophinney/zf2/tree/prototype/mvc-module). The prototype was created as a module (under "modules/Zf2Mvc") in order to also prototype one suggested format for developing modules under ZF2. We had [two](http://zend-framework-community.634137.n4.nabble.com/ZF2-MVC-Prototype-tp3792474p3792474.html) [threads](http://zend-framework-community.634137.n4.nabble.com/Updated-prototype-plus-quickstart-tp3797750p3797750.html) in the mailing list detailing and discussing the prototype. At the time of this writing, all major feedback has been incorporated.

 Building on top of the MVC prototype, I then created a new branch of the [zf-quickstart](https://github.com/weierophinney/zf-quickstart) project that utilizes the new prototype, which also resulted in a [fair bit of discussion](http://zend-framework-community.634137.n4.nabble.com/Updated-prototype-plus-quickstart-tp3797750p3797750.html).

 In IRC, [Rob Allen](http://akrabat.com) and [Evan Coury](http://evan.pro) took the prototype and quickstart as a starting point for a "Module Manager" component that could discover module configuration, autoloading, etc. Evan quickly developed a new module, "Zf2Module," for exactly this purpose (code is [on GitHub](https://github.com/EvanDotPro/zf2/tree/prototype/mvc-module/modules/Zf2Module). After a few revisions, he created a ["sandbox" project](https://github.com/EvanDotPro/zf2-sandbox) that illustrates how one might start a project and gradually add modules to it in order to enable new features. Included in the project is a basic homepage and error page as a discrete module, based on the quickstart; a "user" module for simple user authentication and registration; and the guest book from the quickstart.

 **The tl;dr**: Lots of momentum on the MVC front, with viable MVC and module system prototypes.

Options, configuration, and what? Oh, My!
-----------------------------------------

 Early last week, [Ralph](http://ralphschindler.com) wrote up [a proposal for configuration](http://framework.zend.com/wiki/display/ZFDEV2/RFC+-+Object+instantiation+and+configuration) in ZF2, and [opened a thread in the mailing list](http://zend-framework-community.634137.n4.nabble.com/RFC-ZF2-Object-Instantiation-And-Configuration-tp3794736p3794736.html). The response was fairly heated, and resulted in [a counter-proposal](http://framework.zend.com/wiki/display/ZFDEV2/RFC+-+better+configuration+for+components) within an hour or two by Artur Bodera, followed by [more discussion on the list](http://zend-framework-community.634137.n4.nabble.com/RFC-object-based-configuration-for-components-was-unified-API-tp3797085p3797085.html), and then [even more discussion](http://zend-framework-community.634137.n4.nabble.com/ZF2-Option-Arrays-vs-Parameter-Objects-tp3796184p3796184.html).

 After the dust settled, the basic consensus appears to be:

- Denote hard dependencies in the constructor (if no sane default is likely)
- Aggregate "soft" dependencies (i.e., optional configuration) as component-specific "configuration objects", which will allow:
- Moving option validation into configuration objects
- Resulting in fewer necessary internal variables (pull these options from the config object)
- Re-use of configuration objects with many instances
- Ability to create config object extensions with application-specific defaults
- Better hinting for IDEs
- Potentially easier to document options


 However, we still need to vote on this topic. (Hint: I'll be proposing it for this week's IRC meeting.)

Pull Requests, Sweet Pull Requests
----------------------------------

 I managed to merge in something like 50 new pull requests in the past week. Keep 'em coming!

The Future
----------

 We've been reviewing options for distributing ZF and potentially modules. At this time, candidates include [PEAR](http://pear.php.net), [ Pyrus](http://pear2.php.net), [Composer](http://packagist.org), or going home-grown. We've been reviewing our options, and doing some prototyping, and hope to have a recommendation this week.

 I've been working on some proposals surrounding the View layer, and getting some initial feedback from interested parties before posting some RFCs; they should be posted early this week, however.

 There are new RFCs and discussions erupting daily on the mailing list and in the #zftalk.2 IRC channel on Freenode; I encourage you to subscribe to the former and join in the latter so that you can participate.