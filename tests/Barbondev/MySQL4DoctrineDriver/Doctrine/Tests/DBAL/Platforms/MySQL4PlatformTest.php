<?php

namespace Barbondev\MySQL4DoctrineDriver\Doctrine\Tests\DBAL\Platforms;

use Doctrine\Tests\DBAL\Platforms\AbstractMySQLPlatformTestCase;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\TableDiff;
use Doctrine\DBAL\Schema\Index;
use Barbondev\MySQL4DoctrineDriver\Doctrine\DBAL\Platforms\MySQL4Platform;

/**
 * Class MySQL4PlatformTest
 *
 * @package Barbondev\MySQL4DoctrineDriver\Doctrine\Tests\DBAL\Platforms
 * @author Ashley Dawson <ashley.dawson@barbon.com>
 */
class MySQL4PlatformTest extends AbstractMySQLPlatformTestCase
{
    public function createPlatform()
    {
        return new MySQL4Platform();
    }

    public function testGenerateMixedCaseTableCreate()
    {
        $table = new Table("Foo");
        $table->addColumn("Bar", "integer");

        $sql = $this->_platform->getCreateTableSQL($table);
        $this->assertEquals('CREATE TABLE Foo (Bar INT NOT NULL) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB', array_shift($sql));
    }

    public function testGeneratesSqlSnippets()
    {
        $this->assertEquals('RLIKE', $this->_platform->getRegexpExpression(), 'Regular expression operator is not correct');
        $this->assertEquals('`', $this->_platform->getIdentifierQuoteCharacter(), 'Quote character is not correct');
        $this->assertEquals('CONCAT(column1, column2, column3)', $this->_platform->getConcatExpression('column1', 'column2', 'column3'), 'Concatenation function is not correct');
    }

    public function testGeneratesTransactionsCommands()
    {
        $this->assertEquals(
            'SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED',
            $this->_platform->getSetTransactionIsolationSQL(\Doctrine\DBAL\Connection::TRANSACTION_READ_UNCOMMITTED),
            ''
        );
        $this->assertEquals(
            'SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED',
            $this->_platform->getSetTransactionIsolationSQL(\Doctrine\DBAL\Connection::TRANSACTION_READ_COMMITTED)
        );
        $this->assertEquals(
            'SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ',
            $this->_platform->getSetTransactionIsolationSQL(\Doctrine\DBAL\Connection::TRANSACTION_REPEATABLE_READ)
        );
        $this->assertEquals(
            'SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE',
            $this->_platform->getSetTransactionIsolationSQL(\Doctrine\DBAL\Connection::TRANSACTION_SERIALIZABLE)
        );
    }

    public function testGeneratesDDLSnippets()
    {
        $this->assertEquals('SHOW DATABASES', $this->_platform->getListDatabasesSQL());
        $this->assertEquals('CREATE DATABASE foobar', $this->_platform->getCreateDatabaseSQL('foobar'));
        $this->assertEquals('DROP DATABASE foobar', $this->_platform->getDropDatabaseSQL('foobar'));
        $this->assertEquals('DROP TABLE foobar', $this->_platform->getDropTableSQL('foobar'));
    }

    public function testGeneratesTypeDeclarationForIntegers()
    {
        $this->assertEquals(
            'INT',
            $this->_platform->getIntegerTypeDeclarationSQL(array())
        );
        $this->assertEquals(
            'INT AUTO_INCREMENT',
            $this->_platform->getIntegerTypeDeclarationSQL(array('autoincrement' => true)
            ));
        $this->assertEquals(
            'INT AUTO_INCREMENT',
            $this->_platform->getIntegerTypeDeclarationSQL(
                array('autoincrement' => true, 'primary' => true)
            ));
    }

    public function testGeneratesTypeDeclarationForStrings()
    {
        $this->assertEquals(
            'CHAR(10)',
            $this->_platform->getVarcharTypeDeclarationSQL(
                array('length' => 10, 'fixed' => true)
            ));
        $this->assertEquals(
            'VARCHAR(50)',
            $this->_platform->getVarcharTypeDeclarationSQL(array('length' => 50)),
            'Variable string declaration is not correct'
        );
        $this->assertEquals(
            'VARCHAR(255)',
            $this->_platform->getVarcharTypeDeclarationSQL(array()),
            'Long string declaration is not correct'
        );
    }

    public function testPrefersIdentityColumns()
    {
        $this->assertTrue($this->_platform->prefersIdentityColumns());
    }

    public function testSupportsIdentityColumns()
    {
        $this->assertTrue($this->_platform->supportsIdentityColumns());
    }

    public function testDoesSupportSavePoints()
    {
        $this->assertTrue($this->_platform->supportsSavepoints());
    }

    public function testUniquePrimaryKey()
    {
        $keyTable = new Table("foo");
        $keyTable->addColumn("bar", "integer");
        $keyTable->addColumn("baz", "string");
        $keyTable->setPrimaryKey(array("bar"));
        $keyTable->addUniqueIndex(array("baz"));

        $oldTable = new Table("foo");
        $oldTable->addColumn("bar", "integer");
        $oldTable->addColumn("baz", "string");

        $c = new \Doctrine\DBAL\Schema\Comparator;
        $diff = $c->diffTable($oldTable, $keyTable);

        $sql = $this->_platform->getAlterTableSQL($diff);

        $this->assertEquals(array(
            "ALTER TABLE foo ADD PRIMARY KEY (bar)",
            "CREATE UNIQUE INDEX UNIQ_8C73652178240498 ON foo (baz)",
        ), $sql);
    }

    public function testModifyLimitQuery()
    {
        $sql = $this->_platform->modifyLimitQuery('SELECT * FROM user', 10, 0);
        $this->assertEquals('SELECT * FROM user LIMIT 10 OFFSET 0', $sql);
    }

    public function testModifyLimitQueryWithEmptyOffset()
    {
        $sql = $this->_platform->modifyLimitQuery('SELECT * FROM user', 10);
        $this->assertEquals('SELECT * FROM user LIMIT 10', $sql);
    }

    public function testGetDateTimeTypeDeclarationSql()
    {
        $this->assertEquals("DATETIME", $this->_platform->getDateTimeTypeDeclarationSQL(array('version' => false)));
        $this->assertEquals("TIMESTAMP", $this->_platform->getDateTimeTypeDeclarationSQL(array('version' => true)));
        $this->assertEquals("DATETIME", $this->_platform->getDateTimeTypeDeclarationSQL(array()));
    }

    public function testChangeIndexWithForeignKeys()
    {
        $index = new Index("idx", array("col"), false);
        $unique = new Index("uniq", array("col"), true);

        $diff = new TableDiff("test", array(), array(), array(), array($unique), array(), array($index));
        $sql = $this->_platform->getAlterTableSQL($diff);
        $this->assertEquals(array("ALTER TABLE test DROP INDEX idx, ADD UNIQUE INDEX uniq (col)"), $sql);

        $diff = new TableDiff("test", array(), array(), array(), array($index), array(), array($unique));
        $sql = $this->_platform->getAlterTableSQL($diff);
        $this->assertEquals(array("ALTER TABLE test DROP INDEX uniq, ADD INDEX idx (col)"), $sql);
    }

    public function getGenerateTableSql()
    {
        return 'CREATE TABLE test (id INT AUTO_INCREMENT NOT NULL, test VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) ' .
        'DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB';
    }

    public function getGenerateTableWithMultiColumnUniqueIndexSql()
    {
        return array(
            'CREATE TABLE test (foo VARCHAR(255) DEFAULT NULL, bar VARCHAR(255) DEFAULT NULL, ' .
            'UNIQUE INDEX UNIQ_D87F7E0C8C73652176FF8CAA (foo, bar)) DEFAULT CHARACTER SET utf8 COLLATE ' .
            'utf8_unicode_ci ENGINE = InnoDB'
        );
    }

    public function getGenerateIndexSql()
    {
        return 'CREATE INDEX my_idx ON mytable (user_name, last_login)';
    }

    public function getGenerateUniqueIndexSql()
    {
        return 'CREATE UNIQUE INDEX index_name ON test (test, test2)';
    }

    public function getGenerateForeignKeySql()
    {
        return 'ALTER TABLE test ADD FOREIGN KEY (fk_name_id) REFERENCES other_table (id)';
    }

    public function getGenerateAlterTableSql()
    {
        return array(
            "ALTER TABLE mytable RENAME TO userlist, ADD quota INT DEFAULT NULL, DROP foo, " .
            "CHANGE bar baz VARCHAR(255) DEFAULT 'def' NOT NULL, CHANGE bloo bloo TINYINT(1) DEFAULT '0' NOT NULL"
        );
    }

    protected function getQuotedColumnInPrimaryKeySQL()
    {
        return array(
            'CREATE TABLE `quoted` (`create` VARCHAR(255) NOT NULL, PRIMARY KEY(`create`)) ' .
            'DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB'
        );
    }

    protected function getQuotedColumnInIndexSQL()
    {
        return array(
            'CREATE TABLE `quoted` (`key` VARCHAR(255) NOT NULL, INDEX IDX_22660D028A90ABA9 (`key`)) ' .
            'DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB'
        );
    }

    protected function getQuotedColumnInForeignKeySQL()
    {
        $this->markTestSkipped('Redundant test, no support for MySQL 4');
    }

    public function testQuotedColumnInIndexPropagation()
    {
        $this->markTestSkipped('Redundant test, no support for MySQL 4');
    }

    public function testQuotedColumnInForeignKeyPropagation()
    {
        $this->markTestSkipped('Redundant test, no support for MySQL 4');
    }
}