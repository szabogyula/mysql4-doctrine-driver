<?php

namespace Barbondev\MySQL4DoctrineDriver\Doctrine\DBAL\Schema;

use Doctrine\DBAL\Schema\Table as DoctrineTable;

/**
 * Class Table
 *
 * @package Barbondev\MySQL4DoctrineDriver\Doctrine\DBAL\Schema
 * @author Ashley Dawson <ashley.dawson@barbon.com>
 */
class Table extends DoctrineTable
{
    /**
     * The shortest name is stripped of the default namespace. All other
     * namespaced elements are returned as full-qualified names.
     *
     * @param string
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
     * The normalized name is full-qualified and lowerspaced. Lowerspacing is
     * actually wrong, but we have to do it to keep our sanity. If you are
     * using database objects that only differentiate in the casing (FOO vs
     * Foo) then you will NOT be able to use Doctrine Schema abstraction.
     *
     * Every non-namespaced element is prefixed with the default namespace
     * name which is passed as argument to this method.
     *
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