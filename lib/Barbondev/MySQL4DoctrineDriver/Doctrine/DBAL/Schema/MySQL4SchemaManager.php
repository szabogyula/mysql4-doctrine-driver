<?php

namespace Barbondev\MySQL4DoctrineDriver\Doctrine\DBAL\Schema;

use Doctrine\DBAL\Schema\MySqlSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Type;
use Barbon\MySQL4DoctrineDriver\Doctrine\DBAL\Schema\Schema;
use Barbon\MySQL4DoctrineDriver\Doctrine\DBAL\Schema\Table;

/**
 * Class MySQL4SchemaManager
 *
 * @package Barbondev\MySQL4DoctrineDriver\Doctrine\DBAL\Schema
 * @author Ashley Dawson <ashley.dawson@barbon.com>
 */
class MySQL4SchemaManager extends MySqlSchemaManager
{
    /**
     * Create a schema instance for the current database.
     *
     * @return Schema
     */
    public function createSchema()
    {
        $sequences = array();
        if($this->_platform->supportsSequences()) {
            $sequences = $this->listSequences();
        }
        $tables = $this->listTables();

        return new Schema($tables, $sequences, $this->createSchemaConfig());
    }

    /**
     * @param  string $tableName
     * @return Table
     */
    public function listTableDetails($tableName)
    {
        $columns = $this->listTableColumns($tableName);
        $foreignKeys = array();
        if ($this->_platform->supportsForeignKeyConstraints()) {
            $foreignKeys = $this->listTableForeignKeys($tableName);
        }
        $indexes = $this->listTableIndexes($tableName);

        return new Table($tableName, $columns, $indexes, $foreignKeys, false, array());
    }

    /**
     * Gets a portable column definition.
     *
     * The database type is mapped to a corresponding Doctrine mapping type.
     *
     * @param $tableColumn
     * @return array
     */
    protected function _getPortableTableColumnDefinition($tableColumn)
    {
        $tableColumn = array_change_key_case($tableColumn, CASE_LOWER);

        $dbType = strtolower($tableColumn['type']);
        $dbType = strtok($dbType, '(), ');

        if (isset($tableColumn['length'])) {
            $length = $tableColumn['length'];
        } else {
            $length = strtok('(), ');
        }

        $fixed = null;

        if ( ! isset($tableColumn['name'])) {
            $tableColumn['name'] = '';
        }

        $scale = null;
        $precision = null;

        $type = $this->_platform->getDoctrineTypeMapping($dbType);

        switch ($dbType) {
            case 'char':
                $fixed = true;
                break;
            case 'float':
            case 'double':
            case 'real':
            case 'numeric':
            case 'decimal':
                if(preg_match('([A-Za-z]+\(([0-9]+)\,([0-9]+)\))', $tableColumn['type'], $match)) {
                    $precision = $match[1];
                    $scale = $match[2];
                    $length = null;
                }
                break;
            case 'tinyint':
            case 'smallint':
            case 'mediumint':
            case 'int':
            case 'integer':
            case 'bigint':
            case 'tinyblob':
            case 'mediumblob':
            case 'longblob':
            case 'blob':
            case 'year':
                $length = null;
                break;
        }

        $length = ((int) $length == 0) ? null : (int) $length;

        $options = array(
            'length'        => $length,
            'unsigned'      => (bool) (strpos($tableColumn['type'], 'unsigned') !== false),
            'fixed'         => (bool) $fixed,
            'default'       => isset($tableColumn['default']) ? $tableColumn['default'] : null,
            'notnull'       => (bool) ($tableColumn['null'] != 'YES'),
            'scale'         => null,
            'precision'     => null,
            'autoincrement' => (bool) (strpos($tableColumn['extra'], 'auto_increment') !== false),
            'comment'       => (isset($tableColumn['comment'])) ? $tableColumn['comment'] : null
        );

        if ($scale !== null && $precision !== null) {
            $options['scale'] = $scale;
            $options['precision'] = $precision;
        }

        return new Column($tableColumn['field'], Type::getType($type), $options);
    }
}