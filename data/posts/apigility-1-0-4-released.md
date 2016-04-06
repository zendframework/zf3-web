---
layout: post
title: Apigility 1.0.4 Released!
date: 2014-08-13 23:30
update: 2014-08-13 23:30
author: Matthew Weier O'Phinney
url_author: http://mwop.net/
permalink: /blog/apigility-1-0-4-released.html
categories:
- blog
- apigility
- released

---

Apigility 1.0.4
===============

We are pleased to announce the immediate availability of Apigility 1.0.4!

- <https://apigility.org/download>

This is our fourth maintenance release of Apigility.

IBM i Support
-------------

This release contains a number of fixes to ensure the ability to use Apigility on IBM i. Among them:

- We are pinning support to Zend Framework 2.3.2 and above, which contains updates supporting DB2:
- Full transaction support.
- Fixed LIMIT support, allowing for paginated DB result sets.
- Fixes to database-backed authentication
- The ability to specify database driver options via the Admin UI. Most DB2  
 connections need additional driver options specified, and you can now do so via the UI.

UI Improvements
---------------

One lingering issue we've had reported is an error when creating APIs: the UI reports an error, but the API has been created. We made several patches that, in aggregate, should resolve these issues going forward:

- We discovered that our promise chains in the Admin UI were not optimally constructed, and could potentially raise errors under the appropriate conditions; these have been fixed.
- We introduced comprehensive cache control headers to prevent client-side caching of Admin API calls.
- We introduced a timeout between successful completion of API creation and deletion calls, and subsequent fetching of the API list from the Admin API. In working with [Julien Guittard](https://github.com/jguittard), we were able to find an optimal timeout that resolves the issue.

Additionally, for those users using Apache to serve the Admin UI and Admin API, we have stopped using backslashes in URI identifiers (Apache rejects URI-encoded slashes by default).

Other fixes were also made that are detailed under the "zf-apigility-admin" header below.

Documentation fixes
-------------------

zf-apigility-documentation was not using the correct configuration key to discover input filters, which meant it was not reporting fields at all. This had further implications for zf-apigility-documentation-swagger, which was then unable to expose models based on those fields. This situation is now resolved.

Collections
-----------

While Apigility has supported retrieving collections in REST services, creating, replacing, updating, or deleting them has been an exercise left to the developer previously. With this release, field definitions can now be used to validate the items passed to collections, giving collections first-class support.

Console
-------

zf-console was extensively updated, with many contributions and ideas from Zend's [Slavey Karadzhov](https://github.com/slaff). These include:

- Simplification of mapping the command name to the route. By default the command name is considered the first argument of the route now.
- Command handlers may now be specified in the configuration via the `handler` key for a command.
- A number of useful CLI-specific filters are now provided, including an `Explode` filter (split comma or other delimited arguments to an array), a `QueryString` filter (specify arguments in query string format), and a `Json` filter (specify arguments in JSON).
- Better error handling and error reporting.
- The ability to generate autocompletion scripts for your CLI commands.

zf-console is shaping up as a capable microframework for CLI commands!

Thank You!
----------

Many thanks to everyone who contributed fixes, big or small, towards this release!

Issues closed:
--------------

### zf-apigility-admin

- [Timeout delay upon API creation and deletion](https://github.com/zfcampus/zf-apigility-admin/pull/220)
- [Introduced timeouts to API create/delete actions](https://github.com/zfcampus/zf-apigility-admin/pull/219)
- [Disable HTTP caching for Admin API](https://github.com/zfcampus/zf-apigility-admin/pull/218)
- [url-encoded backslashes cause issues in Apache](https://github.com/zfcampus/zf-apigility-admin/pull/215)
- [File permissions upon resources files creation](https://github.com/zfcampus/zf-apigility-admin/pull/214)
- [Revise promise chains](https://github.com/zfcampus/zf-apigility-admin/pull/213)
- [Allow defining DB adapter driver options](https://github.com/zfcampus/zf-apigility-admin/pull/212)
- [Resolves #210 by correcting the dead link](https://github.com/zfcampus/zf-apigility-admin/pull/211)
- [Undefined index: input\_filter\_specs](https://github.com/zfcampus/zf-apigility-admin/pull/205)
- [OAuth2 Mongo Adapter cannot be created successfully](https://github.com/zfcampus/zf-apigility-admin/pull/204)
- [zf-hal option 'render\_collections' can break Apigility admin](https://github.com/zfcampus/zf-apigility-admin/pull/196)
- [Feature request: Ability to disable pagination from admin ui](https://github.com/zfcampus/zf-apigility-admin/pull/190)
- [Creating new API fails with "API not found"](https://github.com/zfcampus/zf-apigility-admin/issues/175)
- [Can't Edit OAuth Adapter](https://github.com/zfcampus/zf-apigility-admin/pull/172)

### zf-apigility-documentation

- [Fixed usage of configuration-driven creation of input filters](https://github.com/zfcampus/zf-apigility-documentation/pull/13)

### zf-apigility-documentation-swagger

- [Use service name instead of api name to describe endpoint](https://github.com/zfcampus/zf-apigility-documentation-swagger/pull/6)
- [Add dependency](https://github.com/zfcampus/zf-apigility-documentation-swagger/pull/5)

### zf-apigility-skeleton

- [Bump ZF2 version requirement](https://github.com/zfcampus/zf-apigility-skeleton/pull/76)
- [Prefix config glob path](https://github.com/zfcampus/zf-apigility-skeleton/pull/73)
- [Apache configuration](https://github.com/zfcampus/zf-apigility-skeleton/issues/71)
- [Ensure default Apache site is disabled](https://github.com/zfcampus/zf-apigility-skeleton/pull/67)

### zf-console

- [Added out-of-the-box autocompletion help for all applications based on zf-console](https://github.com/zfcampus/zf-console/pull/11)
- [Better error handling](https://github.com/zfcampus/zf-console/pull/9)
- [Useful filters](https://github.com/zfcampus/zf-console/pull/8)
- [Allow setting handler in route configuration](https://github.com/zfcampus/zf-console/pull/7)
- [Simplify mapping the command name to the route](https://github.com/zfcampus/zf-console/pull/5)

### zf-content-validation

- [Bug: Validation bypassed when POST is empty](https://github.com/zfcampus/zf-content-validation/pull/20)
- [isCollection() method returning true for entities](https://github.com/zfcampus/zf-content-validation/pull/19)
- [Problems concerning validating collections](https://github.com/zfcampus/zf-content-validation/pull/3)

### zf-deploy

- [Remove include of application configuration](https://github.com/zfcampus/zf-deploy/pull/27)
- [Cannot validate deployment.xml if zfdeploy.phar is in a folder with spaces](https://github.com/zfcampus/zf-deploy/pull/26)
- [Updated to use features from latest master of zf-console](https://github.com/zfcampus/zf-deploy/pull/22)
- [Execute composer.phar with the PHP binary running zf-deploy](https://github.com/zfcampus/zf-deploy/pull/21)

### zf-hal

- [422 status when my entity has no identifier on creation](https://github.com/zfcampus/zf-hal/issues/51)
- [Possible issue on HAL collection first link of the paginator.](https://github.com/zfcampus/zf-hal/pull/50)
- [Allow -1 for page size](https://github.com/zfcampus/zf-hal/pull/48)
- [Links in metadata map are no longer honored](https://github.com/zfcampus/zf-hal/pull/47)
- [Update Hal Plugin to support entities with $id = 0](https://github.com/zfcampus/zf-hal/pull/45)
- [Can't return a collection object with embedded entities when content negotiation is set to Json](https://github.com/zfcampus/zf-hal/pull/39)

### zf-mvc-auth

- [deny\_by\_default inverts permission rules](https://github.com/zfcampus/zf-mvc-auth/pull/36)
- 
- [Support for OAuth2 Token in Query String / POST Body](https://github.com/zfcampus/zf-mvc-auth/pull/35)

### zf-oauth2

- [Use content negotiation in AuthController](https://github.com/zfcampus/zf-oauth2/pull/58)
- [Ensure bodyParams is an array before creating request](https://github.com/zfcampus/zf-oauth2/pull/56)
- [Update PdoAdapter.php](https://github.com/zfcampus/zf-oauth2/pull/54)
- [refactored the class to support better reuse when extending the class](https://github.com/zfcampus/zf-oauth2/pull/48)
- [Separate MongoClient creation from MongoAdapter factory](https://github.com/zfcampus/zf-oauth2/issues/46)

### zf-rest:

- [Allow returning entities without identifiers from creation routines](https://github.com/zfcampus/zf-rest/pull/43)
- [Allow Header tied to 'id' param.](https://github.com/zfcampus/zf-rest/pull/39)
- [Can't attach to a resource's event using the SharedEventManager](https://github.com/zfcampus/zf-rest/pull/38)
- [Update RestController to handle entities with $id = 0](https://github.com/zfcampus/zf-rest/pull/36)
- [Can't return a collection object with embedded entities when content negotiation is set to Json](https://github.com/zfcampus/zf-rest/pull/31)