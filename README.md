MySQL 4 Doctrine Driver
=======================

Summary
-------

MySQL 4 driver for interigating your legacy databases from within the Doctrine ORM. We recommend that you upgrade to the latest version of MySQL as soon as you can to avoid the use of this driver!

Installation
------------

    "require": {
        ...
        "barbondev/mysql4-doctrine-driver": "dev-master"
    },

Usage
-----

Doctrine driver to allow ORM access to a MySQL 4.* database.

Symfony `app/config/config.yml` configuration:

    doctrine:
        dbal:
            driver_class: Barbondev\MySQL4DoctrineDriver\Doctrine\DBAL\Driver\PDOMySQL4\Driver
            host:     %database_host%
            port:     %database_port%
            dbname:   %database_name%
            user:     %database_user%
            password: %database_password%
            charset:  UTF8

Note that driver type is replaced with the fully qualified namespace of the MySQL 4.* driver class
