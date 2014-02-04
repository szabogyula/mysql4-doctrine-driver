<?php

namespace Barbon\MySQL4DoctrineDriver\Doctrine\DBAL\Driver\PDOMySQL4;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOMySql\Driver as PDOMySQLDriver;
use Barbon\MySQL4DoctrineDriver\Doctrine\DBAL\Schema\MySQL4SchemaManager;
use Barbon\MySQL4DoctrineDriver\Doctrine\DBAL\Platforms\MySQL4Platform;

/**
 * Class Driver
 *
 * @package Barbon\MySQL4DoctrineDriver\Doctrine\DBAL\Driver\PDOMySQL4
 * @author Ashley Dawson <ashley.dawson@barbon.com>
 */
class Driver extends PDOMySQLDriver
{
    /**
     * {@inheritdoc}
     *
     * @return \Doctrine\DBAL\Platforms\AbstractPlatform The database platform.
     */
    public function getDatabasePlatform()
    {
        return new MySQL4Platform();
    }

    /**
     * {@inheritdoc}
     *
     * @param  \Doctrine\DBAL\Connection $conn
     * @return \Doctrine\DBAL\Schema\AbstractSchemaManager
     */
    public function getSchemaManager(Connection $conn)
    {
        return new MySQL4SchemaManager($conn);
    }

    /**
     * {@inheritdoc}
     *
     * @return string The name of the driver.
     */
    public function getName()
    {
        return 'barbon_pdo_mysql4';
    }
}