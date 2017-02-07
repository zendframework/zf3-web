---
layout: post
title: Use Laravel Homestead per PHP projects
date: 2017-02-07T11:15:00-06:00
author: Enrico Zimuel
url_author: http://www.zimuel.it
permalink: /blog/2017-02-07-laravel-homestead.html
categories:
- blog
- laravel
- php

---

[Laravel Homestead](https://laravel.com/docs/5.4/homestead) is an interesting
project by Laravel community that provides a [Vagrant](https://www.vagrantup.com/)
box for PHP developers. It includes a full set of services for PHP developers,
like Nginx web server, PHP 7.1, MySQL, Postgres, Redis, Memcached, Node, and
more.

One the most interesting feature of this project is the ability to work per
project. This means you can run a vagrant box for your specific PHP project.

## Install the Vagrant box

You need to install the [laravel/homestead](https://atlas.hashicorp.com/laravel/boxes/homestead)
vagrant box before to use it. You can use different provider: [VirtualBox 5.1](https://www.virtualbox.org/wiki/Downloads),
[VMWare](https://www.vmware.com/), or [Parallels](http://www.parallels.com/products/desktop/).

We used VirtualBox and the following command to install the laravel/homestead
box:

```bash
vagrant box add laravel/homestead
```

You will need some minutes for the download of 981 MB.

## Use homestead per PHP project

Once you have installed the laravel/homestead vagrant box we can use it globally
or per PHP project.

If we install homestead per project, we will have a full development server
configured directly on the local folder, without sharing services in different
project. A big plus!

To use homestead per project we need to install the [laravel/homestead](https://github.com/laravel/homestead)
PHP project. This can be done using [composer](https://getcomposer.org/) with
the following command:

```bash
composer require laravel/homestead
```

After the installation, you can execute the `homestead` command to build the
Vagrantfile.

```bash
vendor/bin/homestead make
```
The make command creates `VagrantFile` and `Homestead.yaml` for configuration.

By default, the vagrant box is set up on `192.168.10.10` with the hostname
`homestead.app`. You can change the IP address in `Homestead.yaml` if you want.

The `Homestead.yaml` configuration file contains all the details about the
vagrant box configuration. Here is reported an example:

```
---
ip: "192.168.10.10"
memory: 2048
cpus: 1
hostname: expressive-homestead
name: expressive-homestead
provider: virtualbox

authorize: ~/.ssh/id_rsa.pub

keys:
    - ~/.ssh/id_rsa

folders:
    - map: "/home/enrico/expressive-homestead"
      to: "/home/vagrant/expressive-homestead"

sites:
    - map: homestead.app
      to: "/home/vagrant/expressive-homestead/public"

databases:
    - homestead
```

This configuration file is very simple and intuitive, for instance the folders
to be used are reported in the `folders` section.
The `map` value is the local folder of the project, the `to` value is the folder
on the virtual machine.

If you want to add or change more features in the virtual machine you can used
the `Homestead.yaml` configuration file. For instance, if you prefer to add
MariaDB instead of MySQL, you need to add the `mariadb` option:

```
ip: "192.168.10.10"
memory: 2048
cpus: 1
hostname: expressive-homestead
name: expressive-homestead
provider: virtualbox
mariadb: true

```

This option will remove MySQL and install MariaDB.

To run the vagrant box, you need to update the `/etc/hosts` file on Linux/Mac,
adding the following line:

```
192.168.10.10 homestead.app
```

On Windows, the host file is located in `C:\Windows\System32\drivers\etc\hosts`.

This is the only operation that you have to provide manually. After this last
step, you can run the vagrant box using the command:

```bash
vagrant up
```

If you open a browser to `http://homestead.app` you will see your application
running.

## More information

For more information about Laravel Homestead visit the [official documentation]((https://laravel.com/docs/5.4/homestead))
of the project.
