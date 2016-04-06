---
layout: post
title: 2011-11-11 Dev status update
date: 2011-11-11 19:10
update: 2011-11-11 19:10
author: Matthew Weier O'Phinney
url_author: http://mwop.net/
permalink: /blog/2011-11-11-dev-status-update.html
categories:
- blog
- dev

---

 We've been busy since the last update!

 The last update was during the busy-ness of [ZendCon](http://zendcon.com/), where we [announced the first beta release](http://framework.zend.com/zf2/blog/entry/Zend-Framework-2-0-0beta1-Released) of ZF2. The release was met with a lot of enthusiasm, and we've seen increased usage and testing of ZF2 in the weeks following.

 Since then, let's recap what's been going on in ZF2 development.

IRC Meetings
------------

### 26 October 2011 IRC Meeting

 We held an IRC meeting the Wednesday immediately following ZendCon. During the meeting, we discussed three items: a nascent ACL/RBAC RFC, differences between RFCs and proposals, and module distribution and installation.

 The conclusions were:

- Not enough details as to whether we need to refactor ACL, other than to take advantage of some SPL interfaces and classes. Somebody needs to spearhead this. Additionally, if those changes are made, the few calls for an RBAC component may be moot.
- RFCs are for architectural changes or to discuss refactors/rewrites of _existing_ components; proposals are for _new_ components. Consensus is that we need more action and visibility from the CR-Team, and those on the team that were present took notes and followed up with a meeting.
- Basically, the Modules distribution/instalation RFC is on hold until some other intitiatives (such as the CLI RFC) are finalized.

 [Read the full log.](http://framework.zend.com/wiki/display/ZFDEV2/2011-10-26+Meeting+Log)

### 9 November 2011 IRC Meeting

 Two weeks later (this week!) we had another IRC meeting, covering three separate RFCs: Mail, Log, and CLI.

 Both the Mail and Log RFCs were approved for development, with some questions/changes/additions/etc. highlighted during the meeting.

 The CLI RFC is still somewhat rough and needs additional detail, but is headed in the right direction.

 [Read the full log.](http://framework.zend.com/wiki/display/ZFDEV2/2011-11-09+Meeting+Log)

RFCs
----

 Three new RFCs were added (and discussed):

- [Zend\\Log refactoring](http://framework.zend.com/wiki/display/ZFDEV2/RFC+-+Log+refactoring)
- [Zend\\Mail refactoring](http://framework.zend.com/wiki/display/ZFDEV2/RFC+-+Mail+refactoring)
- [Console refactoring/CLI component/Tool refactoring](http://framework.zend.com/wiki/display/ZFDEV2/RFC+-+CLI)

 As noted in the section on IRC meetings, the Log and Mail RFCs have been approved for development, and are on target for our second beta release. The CLI RFC is still being revised, but is on target for a potential beta3 release.

Development
-----------

 Most development has centered on revisions due to feedback on beta1. In particular, some new ideas have been fleshed out to simplify the module manager, while simultaneously making it more flexible and easier to accomplish initialization tasks (such as retrieving autoloading and configuration artifacts, registering events, etc). You can [view a sample here](https://gist.github.com/1348598). Additionally, Ralph has been working to accommodate a number of additional DI use cases identified by users testing the new MVC.

 Matthew has removed all ZF1 MVC components and pushed them into a new module, under "modules/ZendFramework1Mvc". As part of that work, he also identified all components that had dependencies on the old MVC system (particularly the front controller), and refactored them to remove those dependencies and support dependency injection. This work is on the current master.

 Enrico has been working on adapting the latest version of the Windows Azure SDK to ZF2, as well as addressing it in the Zend\\Cloud\\Infrastructure component. We should see this work hitting master early next week.

 With the Mail and Log RFCs now ratified, development on these will progress, and we should see fresh code for these components next week.

 Finally, we announced that ZF2 contributions no longer require a CLA, effective immediately. Since the announcement, we've seen quite a number of new pull requests from new contributors, and we expect this trend to continue.

Fin
---

 These are exciting times for Zend Framework 2 development, and we encourage you to get involved!