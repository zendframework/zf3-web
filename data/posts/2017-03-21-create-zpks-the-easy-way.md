---
layout: post
title: Create ZPKs the Easy Way
date: 2017-03-21T15:05:00-05:00
author: Enrico Zimuel
url_author: http://www.zimuel.it
permalink: /blog/2017-03-21-create-zpks-the-easy-way.html
categories:
- blog
- deployment
- expressive
- php
- zpk
- zend-server

---

[Zend Server](http://www.zend.com/en/products/zend_server) provides the ability
to deploy applications to a single server or cluster of servers via the
[ZPK](http://files.zend.com/help/Zend-Server/content/application_package.htm)
package format. We have offered [zf-deploy](https://github.com/zfcampus/zf-deploy)
for creating these packages from Zend Framework and Apigility applications, but
how can you create these for Expressive, or, really, _any_ PHP application?

## Requirements

To create the ZPK, you need a few things:

- The `zip` binary. ZPKs are ZIP files with specific artifacts.
- The `composer` binary, so you can install dependencies.
- An `.htaccess` file, if your Zend Server installation is using Apache.
- A `deployment.xml` file.

## htaccess

If you are using Apache, you'll want to make sure that you setup things like
rewrite rules for your application. While this can be done when defining the
vhost in the Zend Server admin UI, using an `.htaccess` file makes it easier to
make changes to the rules between deployments.

The following `.htaccess` file will work for many (most?) PHP projects. Place it
relative to your projects front controller script; in the case of Expressive,
Zend Framework, and Apigility, that would mean `public/index.php`, and thus
`public/.htaccess`.

```apacheconf
RewriteEngine On
# The following rule tells Apache that if the requested filename
# exists, simply serve it.
RewriteCond %{REQUEST_FILENAME} -s [OR]
RewriteCond %{REQUEST_FILENAME} -l [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^.*$ - [NC,L]

# The following rewrites all other queries to index.php. The
# condition ensures that if you are using Apache aliases to do
# mass virtual hosting, the base path will be prepended to
# allow proper resolution of the index.php file; it will work
# in non-aliased environments as well, providing a safe, one-size
# fits all solution.
RewriteCond %{REQUEST_URI}::$1 ^(/.+)(.+)::\2$
RewriteRule ^(.*) - [E=BASE:%1]
RewriteRule ^(.*)$ %{ENV:BASE}index.php [NC,L]
```

## deployment.xml

The `deployment.xml` tells Zend Server about the application you are deploying.
What is listed below will work for Expressive, Zend Framework, and Apigility
applications, and likely a number of other PHP applications. The main things to
pay attention to are:

- The `name` should typically match the application name you've setup in Zend
  Server.
- The `version.release` value should be updated for each release; this allows
  you to use rollback features.
- The `appdir` value is the project root. An empty value indicates the same
  directory as the `deployment.xml` lives in.
- The `docroot` value is the directory from which the vhost will serve files.

So, as an example:

```xml
<?xml version="1.0" encoding="utf-8"?>
<package version="2.0" xmlns="http://www.zend.com/server/deployment-descriptor/1.0">
	<type>application</type>
	<name>API</name>
	<summary>API for all the things!</summary>
	<version>
		<release>1.0</release>
	</version>
	<appdir></appdir>
	<docroot>public</docroot>
</package>
```

## Installing dependencies

When you're ready to build a package, you should install your dependencies.
However, don't install them any old way; install them in a production-ready way.
This means:

- Specifying that composer optimize the autoloader (`--optimize-autoloader`).
- Use production dependencies only (`--no-dev`).
- Prefer distribution packages (versus source installs) (`--prefer-dist`).

So:

```bash
$ composer install --no-dev --prefer-dist --optimize-autoloader
```

## Create the ZPK

Finally, we can now create the ZPK, using the `zip` command:

```bash
$ zip -r api-1.0.0.zpk . -x api-1.0.0.zpk -x '*.git/*'
```

This creates the file `api-1.0.0.zpk` with all contents of the current directory
minus the `.git` directory and the ZPK itself (these are excluded via the `-x`
flags). (You may want/need to specify additional exclusions; the above are
typical, however.)

You can then upload the ZPK to the web interface, or use the [Zend Server
SDK](https://github.com/zend-patterns/ZendServerSDK).

## Simple example: single-directory PoC

Let's say you want to do a proof-of-concept, and will be creating an `index.php`
in the project root to test out an idea. You would use the above `.htaccess`,
but keep it in the project root. Your `deployment.xml` would look the same,
except that the `docroot` value would be empty:

```xml
<?xml version="1.0" encoding="utf-8"?>
<package version="2.0" xmlns="http://www.zend.com/server/deployment-descriptor/1.0">
	<type>application</type>
	<name>POC</name>
	<summary>Proof-of-concept of a very cool idea</summary>
	<version>
		<release>0.1.0</release>
	</version>
	<appdir></appdir>
	<docroot></docroot>
</package>
```

You'd then run:

```bash
$ zip -r poc-0.1.0.zpk . -x poc-0.1.0.zpk
```

Done!

## Fin

ZPKs make creating and staging deployment packages fairly easy &mdash; once you
know how to create the packages. We hope that this post helps demystify the
first steps in creating a ZPK for your application.

[Visit the Zend Server documentation For more information on ZPK
structure.](http://files.zend.com/help/Zend-Server/content/understanding_the_application_package_structure.htm)
