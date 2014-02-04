<?php

namespace Barbon\MySQL4DoctrineDriver\Doctrine\Tests;

/**
 * Class TestUtil
 *
 * @package Barbon\MySQL4DoctrineDriver\Doctrine\Tests
 * @author Ashley Dawson <ashley.dawson@barbon.com>
 */
class TestUtil
{
    /**
     * Gets a <b>real</b> database connection using the following parameters
     * of the $GLOBALS array:
     *
     * 'db_class' : The name of the Doctrine DBAL database driver class to use.
     * 'db_username' : The username to use for connecting.
     * 'db_password' : The password to use for connecting.
     * 'db_host' : The hostname of the database to connect to.
     * 'db_name' : The name of the database to connect to.
     * 'db_port' : The port of the database to connect to.
     *
     * Usually these variables of the $GLOBALS array are filled by PHPUnit based
     * on an XML configuration file. If no such parameters exist, an SQLite
     * in-memory database is used.
     *
     * IMPORTANT:
     * 1) Each invocation of this method returns a NEW database connection.
     * 2) The database is dropped and recreated to ensure it's clean.
     *
     * @return \Doctrine\DBAL\Connection The database connection instance.
     */
    public static function getConnection()
    {
        if (isset($GLOBALS['db_class'], $GLOBALS['db_username'], $GLOBALS['db_password'],
            $GLOBALS['db_host'], $GLOBALS['db_name'], $GLOBALS['db_port'])) {

            $realDbParams = array(
                'driverClass' => $GLOBALS['db_class'],
                'user' => $GLOBALS['db_username'],
                'password' => $GLOBALS['db_password'],
                'host' => $GLOBALS['db_host'],
                'dbname' => $GLOBALS['db_name'],
                'port' => $GLOBALS['db_port']
            );

            if (isset($GLOBALS['db_unix_socket'])) {
                $realDbParams['unix_socket'] = $GLOBALS['db_unix_socket'];
            }

            $conn = \Doctrine\DBAL\DriverManager::getConnection($realDbParams, null, null);
        } else {
            $params = array(
                'driver' => 'pdo_sqlite',
                'memory' => true
            );
            if (isset($GLOBALS['db_path'])) {
                $params['path'] = $GLOBALS['db_path'];
                unlink($GLOBALS['db_path']);
            }
            $conn = \Doctrine\DBAL\DriverManager::getConnection($params);
        }

        if (isset($GLOBALS['db_event_subscribers'])) {
            $evm = $conn->getEventManager();
            foreach (explode(",", $GLOBALS['db_event_subscribers']) AS $subscriberClass) {
                $subscriberInstance = new $subscriberClass();
                $evm->addEventSubscriber($subscriberInstance);
            }
        }

        return $conn;
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    public static function getTempConnection()
    {
        $tmpDbParams = array(
            'driver' => $GLOBALS['tmpdb_type'],
            'user' => $GLOBALS['tmpdb_username'],
            'password' => $GLOBALS['tmpdb_password'],
            'host' => $GLOBALS['tmpdb_host'],
            'dbname' => $GLOBALS['tmpdb_name'],
            'port' => $GLOBALS['tmpdb_port']
        );

        // Connect to tmpdb in order to drop and create the real test db.
        return \Doctrine\DBAL\DriverManager::getConnection($tmpDbParams);
    }
}