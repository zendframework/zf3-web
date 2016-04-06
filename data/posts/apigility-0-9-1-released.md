---
layout: post
title: Apigility 0.9.1 Released!
date: 2014-03-01 19:20
update: 2014-03-01 19:20
author: Matthew Weier O'Phinney
url_author: http://mwop.net/
permalink: /blog/apigility-0-9-1-released.html
categories:
- blog
- apigility
- released

---

Today, we're releasing version 0.9.1 of Apigility! You can grab and test it using one of the following two methods:

- Composer: `composer create-project zfcampus/zf-apigility-skeleton apigility 0.9.1`
- Manual download: 
            wget https://github.com/zfcampus/zf-apigility-skeleton/releases/download/0.9.0/zf-apigility-skeleton-0.9.1.zip
            unzip zf-apigility-skeleton-0.9.1.zip

This release is a maintenance release, fixing two critical issues reported against 0.9.0

Fixes
-----

- [zfcampus/zfoauth2#27](https://github.com/zfcampus/zf-oauth2/issues/27) reported an inability to save OAuth2 adapter details from the Apigility admin UI. These are now corrected.
- [A report on the apigility-users mailing list](https://groups.google.com/a/zend.com/d/msgid/apigility-users/b7723f69-e4cc-4619-84d8-c3dd8c1f93a5%40zend.com) indicated that authorizations performed against REST entities were not working correctly. This was due to an incomplete change from "resource" to "entity" (as noted in the 0.9.0 release notes); the situation is now corrected.

Future
------

 At this point, we turn our attention to stabilizing Zend Framework 2.3.0, on which Apigility will depend, due to features added to that upcoming version.

 Once Zend Framework 2.3.0 is released, we will begin the beta cycle for Apigility 1.0.0. During that timeframe, we will due some additional improvements to the UI, and work to ensure the engine is stable. Additionally, we will document the project, providing documentation for each module, as well as for how the modules work together as a whole. We hope to provide "recipes" for a number of common practices and development and deployment situations.