---
layout: post
title: Apigility 1.0.0beta2 Released!
date: 2014-04-16 20:30
update: 2014-04-16 20:30
author: Matthew Weier O'Phinney
url_author: http://mwop.net/
permalink: /blog/apigility-1-0-0beta2-released.html
categories:
- blog
- apigility
- released

---

We are pleased to announce the immediate availability of Apigility 1.0.0beta2!

- <http://apigility.org/download>

This is our second beta release of Apigility, and introduces extensive documentation, Admin UI performance improvements, and deployment tools!

Documentation
-------------

The primary goal of the beta phase for the Zend Framework team is documenting the project. We've made enormous headway at this point, but we'll let **you** be the judge of that; [visit the documentation yourself](http://apigility.org/documentation)!

Among topics covered:

- A "Getting Started" guide, and a companion, "REST Service Tutorial".
- An API Primer
- Chapters on Authentication/Authorization, Content Validation, and the Documentation features.
- A module-by-module reference guide, detailing configuration options.

New Features
------------

While the beta cycle is primarily around stabilizing the API and Admin UI, we decided one new feature warranted inclusion in version 1: a packaging/deployment tool, [zf-deploy](https://github.com/zfcampus/zf-deploy).

This tool allows you to create packages from your Apigility -- or any ZF2 application -- for deployment. Formats supported include zip, tar, tgz, and zpk (the Zend Server deployment package format). We plan to integrate support for deploying zpk packages soon as well.

Beta2 Updates
-------------

Polishing, polishing, polishing was our mantra for beta2. This included incorporating user feedback, but also scrutinizing the UI and code for consistency issues.

### UI Updates

Following beta1, we had a number of complaints about UI responsiveness, particularly around the "Fields" screen. We did some analysis of the UI, and a lot of work around dynamically loading and unloading DOM in the admin based on what should be visible. As a result, we were able to significantly improve responsiveness. There may be more work to do, but early reports indicate that the changes make the Admin UI usable in situations that previously crashed the browser.

In addition to the performance improvements, we made the following updates:

- On the "Authorizations" screen for each API, if no authentication is currently configured, we display a message to this effect, and link to the authentication screen. Unfortunately, in beta1, that link was invalid; we've fixed this.
- The "Fields" tab received a slight overhaul. We noticed that items with toggles displayed "Yes/No" terminology, but "On/Off" for the actual form input; these now use "Yes/No" verbiage consistently. The "Help" screen could not be dismissed with the `<Esc>` key; it now can. Previously, when hitting `<Enter>` from the "Create New Field" text input, it would raise the "Help" screen; it now properly creates the new field. The "Description" field was moved to the first option displayed for each field, to promote documentation of fields. We also added a "Validation Failure Message" field to allow specifying a unified error message on failed validation (vs. one or more per validator); we also ensured that "blanking" out the data in this field will remove any such message previously set. Finally, filters are now listed before validators, to signal the order in which validation operations occur (filtering/normalization occurs before validation).
- The "Source Code" tab was not properly generating links for files; we've fixed this in beta2.

### Engine Updates

A few improvements were made to the API engine itself:

- The `UnauthorizedListener` registered by the `zf-apigility` module was not registering headers set by the `zf-mvc-auth` module, meaning that the `WWW-Authenticate` header was not propagating. This has been corrected.
- We modified `ZF\ContentNegotiation\JsonModel` to check for `json_encode()` errors, and to raise an exception when one is detected. This prevents situations where an empty response is returned on inability to serialize to JSON.
- `zf-apigility-documentation-swagger` was not returning a `Content-Type` header value of `application/vnd.swagger+json`; it now does.
- We fixed the bcrypt cost in `zf-oauth2` to use the defaults from `Zend\Crypt`.
- We updated the OAuth2 database schema in `zf-oauth2` to match that of the upstream [oauth2-server-php package](https://github.com/bshaffer/oauth2-server-php).
- We now inject the `ZF\Rest\ResourceEvent` with the current MVC request object; you can retrieve it from within your resource class using `$this->getEvent()->getRequest()`. This will give you access to HTTP request headers, query string arguments, etc.
- We no longer allow multiple "self" relational links in `zf-hal`.
- When specifying route parameters for a `zf-hal` metadata map, you can now use a PHP callable as the value; `zf-hal` will invoke that callable with the object for which a link is being generated in order to get the value for that route parameter. This is particularly useful for deterimining identifiers for parent resources.
- We moved the `zf-apiglity-welcome` requirement to be a development-only requirement.

Roadmap
-------

We're excited to get a stable release of Apigility as soon as we possibly can. We feel that both the engine and Admin UI have stabilized significantly, and are targetting a stable release by the end of this month. **During that time, we will be working primarily on additional documentation and critical bugfixes.**

As noted in the beta1 announcement, reaching stability is only the first step, however! We already have contributors making significant headway on features such as "Doctrine-Connected", "Mongo-Connected", and "DB-Autodiscovery" REST services, and we will be debuting these in a 1.1 version not long after we reach version 1.0. Stay tuned!