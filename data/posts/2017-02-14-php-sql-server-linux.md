---
layout: post
title: PHP and SQL Server for Linux
date: 2017-02-14T15:25:00-06:00
author: Enrico Zimuel
url_author: http://www.zimuel.it
permalink: /blog/2017-02-14-php-sql-server-linux.html
categories:
- blog
- sqlsrv
- linux
- php
- vagrant

---

This week we tested the public preview of [Microsoft SQL Server for Linux](https://www.microsoft.com/en-us/sql-server/sql-server-vnext-including-Linux)
using PHP 7 with our component [zendframework/zend-db](https://github.com/zendframework/zend-db).

[Microsoft](https://www.microsoft.com) announced the availability of a public
preview of SQL Server for Linux on the 16th of November, 2016. This new
version of SQL Server has some interesting features such as:

- transparent data encryption;
- always encrypted;
- row level security;
- in-memory tables;
- columnstore indexing;
- native JSON support;
- support for in-database analytics with R-integration.

Moreover, the performance of the new DBMS seems to be very impressive. Microsoft
published a case study with [1.2 million requests per second with In-Memory OLTP
on a single commodity server](https://blogs.msdn.microsoft.com/sqlcat/2016/10/26/how-bwin-is-using-sql-server-2016-in-memory-oltp-to-achieve-unprecedented-performance-and-scale/).

We tested the last preview of SQL Server (CTP1.2 - 14.0.200.24) using a [Vagrant](https://www.vagrantup.com/)
box with Ubuntu 16.04 and 4 GB RAM.

## Install SQL Server on Linux

We followed the instructions list on the [Microsoft website](https://www.microsoft.com/en-us/sql-server/developer-get-started/php-ubuntu)
to install SQL Server for PHP on Ubuntu 16.04.

To ensure optimal performance of SQL Server, the Ubuntu box should have at least
4 GB of memory.

The first step is to add the GPG key for the Microsoft repositories.

```bash
$ sudo su
$ curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add -
$ curl https://packages.microsoft.com/config/ubuntu/16.04/mssql-server.list > /etc/apt/sources.list.d/mssql-server.list
$ exit
```

Then we can update the repository list and install the mssql-server package,
using the following commands:

```bash
$ sudo apt-get update
$ sudo apt-get install mssql-server
```

Now we can run the setup for sqlserver. We will be required to accept the EULA and
choose a password for the *System Administrator* (SA).

```bash
sudo /opt/mssql/bin/sqlservr-setup
```

After the installation, we will have SQL Server running on Linux!

## Install the command line utility for SQL Server

Now that we have the DBMS running, we need a tool to access it. Microsoft
provides a command line tool named `sqlcmd`. This program is very similar to the
MySQL client tool, quite familiar to PHP developers.

To install `sqlcmd`, we need to run the following commands:

```bash
$ sudo su
$ curl https://packages.microsoft.com/config/ubuntu/16.04/prod.list > /etc/apt/sources.list.d/mssql-tools.list
$ exit
$ sudo apt-get update
$ sudo apt-get install msodbcsql mssql-tools unixodbc-dev
$ echo 'export PATH="$PATH:/opt/mssql-tools/bin"' >> ~/.bash_profile
$ echo 'export PATH="$PATH:/opt/mssql-tools/bin"' >> ~/.bashrc
$ source ~/.bashrc
```

> ### msodbcsql
>
> Though the Microsoft documentation does not indicate it, we also installed
> `msodbcsql`; without it, we ran into dependency issues.

If the installation was successful, we can start using the command line tool.
For instance, we can ask for the SQL Server vesion using the following
instruction:

```bash
$ sqlcmd -S localhost -U sa -P yourpassword -Q "SELECT @@VERSION"
```

where `yourpassword` should be replaced with the SA password that you choose
during the SQL Server setup.

This command will return output like the following:

```text
----------------------------------------------------------------
Microsoft SQL Server vNext (CTP1.2) - 14.0.200.24 (X64)
	Jan 10 2017 19:15:28
	Copyright (C) 2016 Microsoft Corporation. All rights reserved.
	on Linux (Ubuntu 16.04.1 LTS)
```

## Install the SQL Server extension for PHP

Next, we need to install the PHP extension for SQL Server. This can be done
using [PECL](https://pecl.php.net/).

> ### PECL
>
> If you do not have PECL installed on Ubuntu, you can install it with the
> following command:
>
> ```bash
> $ sudo apt-get install php-dev
> ```

To install the `sqlsrv` and `pdo_sqlsrv` extensions for PHP, we need to execute
the following commands:

```bash
$ sudo apt-get install unixodbc-dev gcc g++ build-essential
$ sudo pecl install sqlsrv pdo_sqlsrv
```

Finally, we need to add the directives `extension=pdo_sqlsrv.so` and
`extension=sqlsrv.so` to our PHP configuration (generally `php.ini`). In our
case, running Ubuntu (or any other Debian-flavored distribution) we have the
PHP configuration files stored in `/etc/php/7.0/mods-available`. We can create
`sqlsrv.ini` and `pdo_sqlsrv.ini` containing the respective configurations. As
a last step, we need to link these configurations to our specific PHP environments.
For this, you can have two choices:

- For Ubuntu, you can use the `phpenmod` command.
- Alternately, you can symlink to the appropriate directory.

For our purposes, we are using PHP 7.0, from the CLI SAPI, so we can do either of the following:

```bash
# Using phpenmod:
$ sudo phpenmod -v 7.0 -s cli sqlsrv pdo_sqlsrv
# Manually symlinking:
$ sudo ln -s /etc/php/7.0/mods-available/sqlsrv.ini /etc/php/7.0/cli/conf.d/20-sqlsrv.ini
$ sudo ln -s /etc/php/7.0/mods-available/pdo_sqlsrv.ini /etc/php/7.0/cli/conf.d/20-pdo_sqlsrv.ini
```

## Integration tests with zend-db

We used the above information to add support for SQL Server to the
[zendframework/zend-db](https://github.com/zendframework/zend-db) vagrant
configuration, used by developers to test against the various database platforms
we support.

We updated the [Vagrantfile](https://github.com/zendframework/zend-db/blob/master/Vagrantfile),
enabling integration tests for MySQL, PostgreSQL and SQL Server on Linux, to
read as follows:

```ruby
$install_software = <<SCRIPT
export DEBIAN_FRONTEND=noninteractive
apt-get -yq update

# INSTALL PostgreSQL
apt-get -yq install postgresql

# Allow external connections to PostgreSQL as postgres
sed -i "s/#listen_addresses = 'localhost'/listen_addresses = '*'/" /etc/postgresql/9.5/main/postgresql.conf
sed -i "s/peer/trust/" /etc/postgresql/9.5/main/pg_hba.conf
echo 'host all all 0.0.0.0/0 trust' >> /etc/postgresql/9.5/main/pg_hba.conf
service postgresql restart

# INSTALL MySQL
debconf-set-selections <<< "mysql-server mysql-server/root_password password Password123"
debconf-set-selections <<< "mysql-server mysql-server/root_password_again password Password123"
apt-get -yq install mysql-server

# Allow external connections to MySQL as root (with password Password123)
sed -i 's/127\.0\.0\.1/0\.0\.0\.0/g' /etc/mysql/mysql.conf.d/mysqld.cnf
mysql -u root -pPassword123 -e 'USE mysql; UPDATE `user` SET `Host`="%" WHERE `User`="root" AND `Host`="localhost"; DELETE FROM `user` WHERE `Host` != "%" AND `User`="root"; FLUSH PRIVILEGES;'
service mysql restart

# INSTALL SQL Server
# More info here: https://www.microsoft.com/en-us/sql-server/developer-get-started/php-ubuntu

curl -s https://packages.microsoft.com/keys/microsoft.asc | apt-key add -
curl -s https://packages.microsoft.com/config/ubuntu/16.04/mssql-server.list > /etc/apt/sources.list.d/mssql-server.list
apt-get -yq update
apt-get -yq install mssql-server
printf "YES\nPassword123\nPassword123\ny\ny" | /opt/mssql/bin/sqlservr-setup

curl -s https://packages.microsoft.com/config/ubuntu/16.04/prod.list > /etc/apt/sources.list.d/mssql-tools.list
apt-get -yq update
ACCEPT_EULA=Y apt-get -yq install msodbcsql mssql-tools unixodbc-dev
echo 'export PATH="$PATH:/opt/mssql-tools/bin"' >> /home/vagrant/.bash_profile
echo 'export PATH="$PATH:/opt/mssql-tools/bin"' >> /home/vagrant/.bashrc
source /home/vagrant/.bashrc
SCRIPT


$setup_vagrant_user_environment = <<SCRIPT
if ! grep "cd /vagrant" /home/vagrant/.profile > /dev/null; then
  echo "cd /vagrant" >> /home/vagrant/.profile
fi
SCRIPT

Vagrant.configure(2) do |config|
  config.vm.box = 'bento/ubuntu-16.04'
  config.vm.provider "virtualbox" do |v|
    v.memory = 4096
    v.cpus = 2
  end

  config.vm.network "private_network", ip: "192.168.20.20"

  config.vm.provision 'shell', inline: $install_software
  config.vm.provision 'shell', privileged: false, inline: '/vagrant/.ci/mysql_fixtures.sh'
  config.vm.provision 'shell', privileged: false, inline: '/vagrant/.ci/pgsql_fixtures.sh'
  config.vm.provision 'shell', privileged: false, inline: '/vagrant/.ci/sqlsrv_fixtures.sh'
  config.vm.provision 'shell', inline: $setup_vagrant_user_environment
end
```

This vagrant configuration installs Ubuntu 16.04 with 4 GB of RAM and the
following databases (*user* and *password* are reported in parenthesis):

- MySQL 5.7.17 (*root*/*Password123*)
- PostgreSQL 9.5 (*postgres*/*postgres*)
- SQL Server 14.0.200.24 (*sa*/*Password123*)

We can use this virtual machine to run [PHPUnit](https://phpunit.de/) for the
zend-db integration tests.  If you want to test this vagrant box, you can clone
the zend-db repository and run the following command:

```bash
$ vagrant up
```

This creates a VM running with IP `192.168.20.20`.

## More information

In this post, we've demonstrated how to install the public preview release of
SQL Server on Ubuntu.  Microsoft also provides support for other Linux
distributions, such as Red Hat and Suse.

You can also install this new SQL Server preview on Windows, macOS, Azure or
using a preconfigured Docker container.

Get more information on SQL Server for Linux from the [official website](https://www.microsoft.com/en-us/sql-server/sql-server-vnext-including-Linux).
Find specific information on how to use SQL Server with PHP
[here](https://www.microsoft.com/en-us/sql-server/developer-get-started/php-ubuntu).
