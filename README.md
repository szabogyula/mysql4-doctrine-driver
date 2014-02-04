Barbon Development
==================

Doctrine MySQL 4 Driver
-------------------------

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
