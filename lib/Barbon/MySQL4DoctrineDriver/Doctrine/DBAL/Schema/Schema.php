<?php

namespace Barbon\MySQL4DoctrineDriver\Doctrine\DBAL\Schema;

use Doctrine\DBAL\Schema\Schema as DoctrineSchema;
use Doctrine\DBAL\Schema\SchemaException;

/**
 * Class Schema
 *
 * @package Barbon\MySQL4DoctrineDriver\Doctrine\DBAL\Schema
 * @author Ashley Dawson <ashley.dawson@barbon.com>
 */
class Schema extends DoctrineSchema
{
    /**
     * {@inheritdoc}
     *
     * @param string $tableName
     * @return \Doctrine\DBAL\Schema\Table
     * @throws SchemaException
     */
    public function getTable($tableName)
    {
        $tableName = $this->getFullQualifiedAssetNameDenormalised($tableName);
        if (!isset($this->_tables[$tableName])) {
            throw SchemaException::tableDoesNotExist($tableName);
        }

        return $this->_tables[$tableName];
    }

    /**
     * {@inheritdoc}
     *
     * @param string $tableName
     * @return bool|DoctrineSchema
     */
    public function hasTable($tableName)
    {
        $tableName = $this->getFullQualifiedAssetNameDenormalised($tableName);
        return isset($this->_tables[$tableName]);
    }

    /**
     * {@inheritdoc}
     *
     * @param $sequenceName
     * @return bool
     */
    public function hasSequence($sequenceName)
    {
        $sequenceName = $this->getFullQualifiedAssetNameDenormalised($sequenceName);
        return isset($this->_sequences[$sequenceName]);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $sequenceName
     * @return \Doctrine\DBAL\Schema\Sequence
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function getSequence($sequenceName)
    {
        $sequenceName = $this->getFullQualifiedAssetNameDenormalised($sequenceName);
        if(!$this->hasSequence($sequenceName)) {
            throw SchemaException::sequenceDoesNotExist($sequenceName);
        }
        return $this->_sequences[$sequenceName];
    }

    /**
     * {@inheritdoc}
     *
     * @param string $tableName
     * @return $this|DoctrineSchema
     */
    public function dropTable($tableName)
    {
        $tableName = $this->getFullQualifiedAssetNameDenormalised($tableName);
        $table = $this->getTable($tableName);
        unset($this->_tables[$tableName]);
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $sequenceName
     * @return $this|DoctrineSchema
     */
    public function dropSequence($sequenceName)
    {
        $sequenceName = $this->getFullQualifiedAssetNameDenormalised($sequenceName);
        unset($this->_sequences[$sequenceName]);
        return $this;
    }

    /**
     * {@see parent::getFullQualifiedAssetName()}
     *
     * @param $name
     * @return string
     */
    protected function getFullQualifiedAssetNameDenormalised($name)
    {
        if ($this->isIdentifierQuoted($name)) {
            $name = $this->trimQuotes($name);
        }
        if (strpos($name, ".") === false) {
            $name = $this->getName() . "." . $name;
        }
        return $name;
    }

    /**
     * {@inheritdoc}
     *
     * @param $defaultNamespaceName
     * @return string
     */
    public function getShortestName($defaultNamespaceName)
    {
        $shortestName = $this->getName();
        if ($this->_namespace == $defaultNamespaceName) {
            $shortestName = $this->_name;
        }
        return $shortestName;
    }

    /**
     * {@inheritdoc}
     *
     * @param $defaultNamespaceName
     * @return string
     */
    public function getFullQualifiedName($defaultNamespaceName)
    {
        $name = $this->getName();
        if ( ! $this->_namespace) {
            $name = $defaultNamespaceName . "." . $name;
        }
        return $name;
    }
}