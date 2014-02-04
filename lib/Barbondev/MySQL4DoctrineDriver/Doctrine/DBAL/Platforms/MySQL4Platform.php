<?php

namespace Barbondev\MySQL4DoctrineDriver\Doctrine\DBAL\Platforms;

use Doctrine\DBAL\Platforms\MySqlPlatform;

/**
 * Class MySQL4Platform
 *
 * @package Barbondev\MySQL4DoctrineDriver\Doctrine\DBAL\Platforms
 * @author Ashley Dawson <ashley.dawson@barbon.com>
 */
class MySQL4Platform extends MySqlPlatform
{
    /**
     * {@inheritDoc}
     *
     * @param string $table
     * @param string|null $currentDatabase
     * @return string
     */
    public function getListTableIndexesSQL($table, $currentDatabase = null)
    {
        if ($currentDatabase) {
            return "SHOW INDEX FROM `{$currentDatabase}`.`{$table}`";
        }

        return "SHOW INDEX FROM `{$table}`";
    }

    /**
     * {@inheritdoc}
     *
     * @param string $table
     * @param string|null $database
     * @return string
     */
    public function getListTableForeignKeysSQL($table, $database = null)
    {
        // Return an empty row with column headers as info schema not in MySQL 4
        return 'SELECT NULL AS `CONSTRAINT_NAME`, NULL AS `COLUMN_NAME`, ' .
        'NULL AS `REFERENCED_TABLE_NAME`, NULL AS `REFERENCED_COLUMN_NAME` LIMIT 0';
    }

    /**
     * {@inheritdoc}
     *
     * @param string $table
     * @param string|null $database
     * @return string
     */
    public function getListTableColumnsSQL($table, $database = null)
    {
        if ($database) {
            return "SHOW COLUMNS FROM `{$database}`.`{$table}`";
        }

        return "DESCRIBE `{$table}`";
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getListTablesSQL()
    {
        return "SHOW TABLES";
    }

    /**
     * Returns the SQL snippet that declares a floating point column of arbitrary precision.
     *
     * @param array $columnDef
     *
     * @return string
     */
    public function getDecimalTypeDeclarationSQL(array $columnDef)
    {
        $columnDef['precision'] = ( ! isset($columnDef['precision']) || empty($columnDef['precision']))
            ? 10 : $columnDef['precision'];
        $columnDef['scale'] = ( ! isset($columnDef['scale']) || empty($columnDef['scale']))
            ? 0 : $columnDef['scale'];

        return 'DECIMAL(' . $columnDef['precision'] . ', ' . $columnDef['scale'] . ')';
    }
}