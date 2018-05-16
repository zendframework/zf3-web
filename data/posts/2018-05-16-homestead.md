---
layout: post
title: Zend Framework/Homestead Integration
date: 2018-05-16T15:15:00-05:00
author: Matthew Weier O'Phinney
url_author: https://mwop.net
permalink: /blog/2018-05-16-homestead.html
categories:
- apigility
- blog
- expressive
- homestead
- laravel
- php
- vagrant
- zendframework

---

Last year, we [wrote about using Laravel Homestead with ZF projects](/blog/2017-02-07-laravel-homestead.html).
Today, we contributed some changes to Homestead to simplify setting it up to
serve Apigility, Expressive, and Zend Framework projects.

As of version 7.6.0, Homestead users can now define sites with any of the
following "type" values:

- apigility
- expressive
- zf

When one of these values is used, Homestead will setup the nginx instance used
by Homestead to properly to work with the project.

## Getting started

Much of what we detailed last year is still true: 

- You will need to add the `laravel/homestead` box to Vagrant: `vagrant box add
  laravel/homestead`.

- You will likely want to use the [vagrant-hostsupdater](https://github.com/cogitatio/vagrant-hostsupdater)
  plugin to Vagrant to facilitate mapping the VM IP address and server name to
  your system hosts file: `vagrant plugin install vagrant-hostsupdater`.

- You will need to temporarily add the `laravel/homestead` package as a
  development dependency to your application: `composer require --dev
  laravel/homestead`.

- You will need to use the Homestead tooling to create a `Vagrantfile` and
  `Homestead.yaml` configuration file: `./vendor/bin/homestead make`.

## Configuring Homestead

Once you have your `Homestead.yaml` file created, you can edit it. The two things
we need specifically are:

- A folder mapping the application root directory to a directory in the vagrant
  image.
- A site definition.

Generally, the folder mapping is already present, and will look something like
the following:

```yaml
folders:
  - map: /home/username/dev/application
    to: /home/vagrant/code
```

If you want the `Homestead.yaml` to be portable, however, you can tell it to map
the _current_ directory, and not a fully qualified path:

```yaml
folders:
  - map: .
    to: /home/vagrant/code
```

Next, we'll look at the site definition. After you first run `homestead make`,
you should have the following:

```yaml
sites:
  - map: homestead.test
    to: /home/vagrant/code/public
```

Let's change this a bit. First, we'll give a new site name, then a site type
(I'll use "expressive" here, but you can change this to "apigility" or "zf"
based on your application), and we'll enable [Z-Ray](http://www.zend.com/en/products/server/z-ray).

```yaml
sites:
  - map: expressive.test
    to: /home/vagrant/code/public
    type: expressive
    zray: "true"
```

> Yes, the correct value for the zray setting is `"true"`; [see this issue for
> details](https://github.com/laravel/homestead/issues/805).

From here, we can finally get running:

```bash
$ vagrant up
```

If you are not using the `vagrant-hostsupdater` plugin, you'll need to add an
entry to your system hosts file:

```text
192.168.10.10 expressive.test
```

## Fin

We're hoping having this support in place will allow Zend Framework zend-mvc,
Apigility, and Expressive developers to create and share development
environments easily between their teams. If you have additional features you
would like enabled by default (e.g., auto-detection of ZF, Apigility, and
Expressive applications by `homestead make`, additional default nginx
configuration, etc.), be sure to swing by the
[Slack](https://zendframework-slack.herokuapp.com) or
[forums](https://discourse.zendframework.com) and ask!

> I want to extend a hearty thank you to [Joe Ferguson](https://www.joeferguson.me/)
> for helping me provide the integration, and guiding me through the
> contribution process for Homestead.
