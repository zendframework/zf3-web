---
layout: post
title: Zend Framework 2.1.3 Released!
date: 2013-02-21 23:00
update: 2013-02-21 23:00
author: Matthew Weier O'Phinney
url_author: http://mwop.net/
permalink: /blog/zend-framework-2-1-3-released.html
categories:
- blog
- released

---

 The Zend Framework community is pleased to announce the immediate availability of Zend Framework 2.1.3! Packages and installation instructions are available at:

- [http://framework.zend.com/downloads/latest](/downloads/latest)

 This release has been pushed out quickly on the heels of 2.1.2 to fix an issue with autoloading PHP version-specific class implementations that was affecting PHP 5.3.3 users. Two other potential regressions were also addressed.

PHP 5.3.3 Users
---------------

 This release finally resolves issues with providing PHP version-specific classes, specifically for PHP 5.3.3 users.

 If you are using [Composer](http://getcomposer.org/) to manager your dependencies, a `composer.phar update` should resolve any issues.

 If you are not, you have two options.

 First, when starting new applications with the [ZendSkeletonApplication](https://github.com/zendframework/ZendSkeletonApplication), class substitution will now happen by default.

 Otherwise, add the following lines to your `init_autoloader.php` file, as indicated by the comments below:

 
    <pre class="highlight"><span style="color: #000000">
    <span style="color: #0000BB"><?php<br></br></span><span style="color: #FF8000">// The following line should be on or around line 34:<br></br></span><span style="color: #007700">if (</span><span style="color: #0000BB">$zf2Path</span><span style="color: #007700">) {<br></br>    if (isset(</span><span style="color: #0000BB">$loader</span><span style="color: #007700">)) {<br></br>        </span><span style="color: #0000BB">$loader</span><span style="color: #007700">-></span><span style="color: #0000BB">add</span><span style="color: #007700">(</span><span style="color: #DD0000">'Zend'</span><span style="color: #007700">, </span><span style="color: #0000BB">$zf2Path</span><span style="color: #007700">);<br></br>    } else {<br></br>        include </span><span style="color: #0000BB">$zf2Path </span><span style="color: #007700">. </span><span style="color: #DD0000">'/Zend/Loader/AutoloaderFactory.php'</span><span style="color: #007700">;<br></br>        </span><span style="color: #0000BB">Zend</span><span style="color: #007700">\</span><span style="color: #0000BB">Loader</span><span style="color: #007700">\</span><span style="color: #0000BB">AutoloaderFactory</span><span style="color: #007700">::</span><span style="color: #0000BB">factory</span><span style="color: #007700">(array(<br></br>            </span><span style="color: #DD0000">'Zend\Loader\StandardAutoloader' </span><span style="color: #007700">=> array(<br></br>                </span><span style="color: #DD0000">'autoregister_zf' </span><span style="color: #007700">=> </span><span style="color: #0000BB">true<br></br>            </span><span style="color: #007700">)<br></br>        ));<br></br><br></br>        </span><span style="color: #FF8000">// Add the following two lines:<br></br>        </span><span style="color: #007700">require </span><span style="color: #0000BB">$zf2Path </span><span style="color: #007700">. </span><span style="color: #DD0000">'/Zend/Stdlib/compatibility/autoload.php'</span><span style="color: #007700">;<br></br>        require </span><span style="color: #0000BB">$zf2Path </span><span style="color: #007700">. </span><span style="color: #DD0000">'/Zend/Session/compatibility/autoload.php'</span><span style="color: #007700">;<br></br>    }<br></br>}<br></br></span>
    </span>


Routing Fixes
-------------

 Two fixes to routing were made after discovering potential regressions.

 The first was to hostname routing. Changes were introduced in 2.1.2 to make matching optional nested subdomains possible; unfortunately, this broke cases where the primary domain was specified. A fix has been included in 2.1.3 that fixes the regression (while simultaneously allowing the new behavior).

 A bug in console routing was also uncovered; camelCased or MixedCase options were allowed in route definitions, but route matching was normalizing options to lowercase, causing false negative matches. This was fixed for 2.1.3.

Changelog
---------

 Below are links to the issues addressed.

- [3714: Zend\\Stdlib\\ArrayObject::offsetExists() returning by reference](https://github.com/zendframework/zf2/issues/3714)
- [3855: Fix #3852](https://github.com/zendframework/zf2/issues/3855)
- [3856: Simple route case insensitive](https://github.com/zendframework/zf2/issues/3856)

Thank You!
----------

 I'd like to thank those that tested the PHP 5.3.3 autoloading fixes, as well as Nick Calugar for providing the fix to hostname routing and Michael Gallego for the fixes to console routing.

Roadmap
-------

 Maintenance releases happen monthly on the third Wednesday; expect version 2.1.4 to drop 20 March 2013. We're also gearing up for version 2.2.0, which we are targetting at the end of April 2013.