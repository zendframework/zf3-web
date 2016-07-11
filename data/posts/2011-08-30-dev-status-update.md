---
layout: post
title: 2011-08-30 Dev status update
date: 2011-08-30T22:20:00Z
update: 2011-08-30T22:20:00Z
author: Matthew Weier O'Phinney
url_author: https://mwop.net/
permalink: /blog/2011-08-30-dev-status-update.html
categories:
- blog
- dev

---

 Zend Framework status update for the week of 22 - 29 August 2011.

 Since the last community update, we've had a number of successes... as well as setbacks.

 First, completion of the initial HTTP component development took a bit longer than anticipated. As a team we felt the need to ensure that we had at least the basic documentation covered, and in doing so, uncovered additional use cases that needed fixing. We felt this was time well-invested however, as most code currently using `Zend\Http\Client` should work with little to no modification from the original --- while sporting a much better design that better separates concerns between a request object, response object, and the client invoking them.

 This allows us to announce a new developer snapshot, 2.0.0dev4:

- Zip: <http://framework.zend.com/releases/ZendFramework-2.0.0dev4/ZendFramework-2.0.0dev4.zip>
- Tar: <http://framework.zend.com/releases/ZendFramework-2.0.0dev4/ZendFramework-2.0.0dev4.tar.gz>

 Also in this release, we were able to convert documentation to DocBook 5, `Zend\Dojo` was brought up-to-date with changes in ZF1, and much, much more due to the efforts of a large number of contributors who have submitted an unprecedented number of pull requests in the past several weeks.

 Second, we also had a number of infrastructure issues. Our mailing list went black for 3-4 days, and no real solution was found. However, the pipes appear to be fully open at this point, and we've had some great discussion over the weekend and early this week.

RFCs
----

 A couple of RFCs have been posted:

- [RFC - What will the ZF2 distribution include?](http://framework.zend.com/wiki/pages/viewpage.action?pageId=43745438)
- [Mailing list discussion](http://zend-framework-community.634137.n4.nabble.com/RFC-What-will-the-ZF2-distribution-include-tp3763888p3763888.html)

- [RFC - ZF2 Modules](http://framework.zend.com/wiki/display/ZFDEV2/RFC+-+ZF2+Modules)
  - [Mailing list discussion](http://zend-framework-community.634137.n4.nabble.com/RFC-ZF2-Modules-tp3776616p3776616.html)

 If you have any input, we'd appreciate having it ASAP, so that we can finalize these and move on to defining discrete development tasks.

IRC Meeting this week
---------------------

 We have another IRC meeting at 17:00 UTC on 31 Aug 2011. The [agenda is on the wiki](http://framework.zend.com/wiki/display/ZFDEV2/2011-08-31+Meeting+Agenda), and we invite you to add/comment/vote on topics prior to the meeting. Votes will be closed approximately 1 hour prior to the meeting.

Current development
-------------------

 We're planning on working on a few items in the next week or so, roughly in order of priority:

- Creation of a dedicated branch for the MVC prototype currently in the zf-quickstart project.
- Investigation of [Pyrus](http://pear2.php.net) to see if it can meet the requirements set forth by the community on both distribution of the framework as well as potentially modules.
- Investigation of [phar](http://php.net/phar) as a foil to the above (but more particularly for module distribution).
- Continued collaboration on the Router.
