---
layout: post
title: Using Laravel Homestead with Zend Framework Projects
date: 2017-02-07T12:35:00-06:00
author: Enrico Zimuel
url_author: http://www.zimuel.it
permalink: /blog/2017-02-07-laravel-homestead.html
categories:
- blog
- homestead
- laravel
- php
- vagrant

---

[Laravel Homestead](https://laravel.com/docs/5.4/homestead) is an interesting
project by the Laravel community that provides a [Vagrant](https://www.vagrantup.com/)
box for PHP developers. It includes a full set of services for PHP developers,
such as the Nginx web server, PHP 7.1, MySQL, Postgres, Redis, Memcached, Node, and
more.

One the most interesting features of this project is the ability to enable it per
project. This means you can run a vagrant box for your specific PHP project.

In this post, we'll examine using it for Zend Framework MVC, Expressive, and
Apigility projects. In each case, installation and usage is exactly the same.

## Install the Vagrant box

The first step is to install the [laravel/homestead](https://atlas.hashicorp.com/laravel/boxes/homestead)
vagrant box. This box works with a variety of providers: [VirtualBox 5.1](https://www.virtualbox.org/wiki/Downloads),
[VMWare](https://www.vmware.com/), or [Parallels](http://www.parallels.com/products/desktop/).

We used VirtualBox and the following command to install the laravel/homestead
box:

```bash
$ vagrant box add laravel/homestead
```

The box is 981 MB, so it will take some minutes to download.

Homestead, by default, uses the host name `homestead.app`, and requires that you
update your system hosts file to point that domain to the virtual machine IP
address. To faciliate that, Homestead provides integration with the
[vagrant-hostsupdater](https://github.com/cogitatio/vagrant-hostsupdater)
Vagrant plugin. We recommend installing that before your initial run of the
virtual machine:

```bash
$ vagrant plugin install vagrant-hostsupdater
```

## Use Homestead in ZF projects

Once you have installed the laravel/homestead vagrant box, you can use it globally
or per project.

If we install Homestead per-project, we will have a full development server
configured directly in the local folder, without sharing services with other
projects. This is a big plus!

To use Homestead per-project, we need to install the [laravel/homestead](https://github.com/laravel/homestead)
package within our Zend Framework, Apigility, or Expressive project. This can be
done using [Composer](https://getcomposer.org/) with the following command:

```bash
$ composer require --dev laravel/homestead
```

After installation, execute the `homestead` command to build the `Vagrantfile`:

```bash
$ vendor/bin/homestead make
```

This command creates both the `VagrantFile` and a `Homestead.yaml` configuration
file.

## Configuring Homestead

By default, the vagrant box is set up at address `192.168.10.10` with the hostname
`homestead.app`. You can change the IP address in `Homestead.yaml` if you want,
as well as the hostname (via the `sites[].map` key).

The `Homestead.yaml` configuration file contains all details about the
vagrant box configuration. The following is an example:

```yaml
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

This configuration file is very simple and intuitive; for instance, the folders
to be used are reported in the `folders` section; the `map` value is the local
folder of the project, the `to` value is the folder on the virtual machine.

If you want to add or change more features in the virtual machine you can used
the `Homestead.yaml` configuration file. For instance, if you prefer to add
MariaDB instead of MySQL, you need to add the `mariadb` option:

```yaml
ip: "192.168.10.10"
memory: 2048
cpus: 1
hostname: expressive-homestead
name: expressive-homestead
provider: virtualbox
mariadb: true
```

This option will remove MySQL and install MariaDB.

> ### SSH keys managed by GPG
>
> One of our team uses the gpg-agent as an ssh-agent, which caused some
> configuration problems initially, as the `~/.ssh/id_rsa` and its `.pub`
> sibling were not present.
>
> When using gpg-agent for serving SSH keys, you can export the key using
> `ssh-add -L`. This may list several keys, but you should be able to find the
> correct one. Copy it to the file `~/.ssh/gpg_key.pub`, and then copy that file
> to `~/.ssh/gpg_key.pub.pub`. Update the `Homestead.yaml` file to reflect
> these new files:
>
> ```yaml
> authorize: ~/.ssh/gpg_key.pub.pub
> keys:
>     - ~/.ssh/gpg_key.pub
> ```
>
> The gpg-agent will take care of sending the appropriate key from there.

## Running Homestead

To run the vagrant box, execute the following within your project root:

```bash
$ vagrant up
```

If you open a browser to `http://homestead.app` you should now see your
application running.

> ### Manually managing your hosts file
>
> If you chose not to use vagrant-hostsupdater, you will need to update your
> system hosts file.
>
> On Linux and Mac, update the `/etc/hosts` file to add the following line:
> 
> ```text
> 192.168.10.10 homestead.app
> ```
> On Windows, the host file is located in `C:\Windows\System32\drivers\etc\hosts`.

## More information

We've tested this setup with each of the Zend Framework zend-mvc skeleton
application, Apigility, and Expressive, and found the setup "just worked"! We
feel it provides excellent flexibility in setting up development environments,
giving developers a wide range of tools and technologies to work with as they
develop applications.

For more information about Laravel Homestead, visit the
[official documentation](https://laravel.com/docs/5.4/homestead)
of the project.
