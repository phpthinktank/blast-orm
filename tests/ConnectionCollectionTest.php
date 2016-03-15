<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 26.11.2015
 * Time: 11:34
 */

namespace Blast\Tests\Orm;


use Blast\Orm\ConnectionManager;
use Blast\Orm\ConnectionManagerInterface;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\DriverManager;

class ConnectionCollectionTest extends \PHPUnit_Framework_TestCase
{
    protected $dsn = [
        'url' => 'sqlite:///:memory:',
        'memory' => 'true'
    ];

    public function testImplementsContainerCollectionInterface()
    {
        $this->assertTrue(is_subclass_of(ConnectionManager::class, ConnectionManagerInterface::class));
    }

    public function testAddConnectionString()
    {
        ConnectionManager::getInstance()->add('sqlite:///:memory:', __METHOD__);
        $this->assertInstanceOf(Connection::class, ConnectionManager::getInstance()->get(__METHOD__));
    }

    public function testAddConnectionArray()
    {
        ConnectionManager::getInstance()->add($this->dsn, __METHOD__);

        $this->assertInstanceOf(Connection::class, ConnectionManager::getInstance()->get(__METHOD__));
    }

    public function testAddConnectionObject()
    {
        $dbalConfiguration = new Configuration();
        $connection = DriverManager::getConnection($this->dsn, $dbalConfiguration);

        ConnectionManager::getInstance()->add($connection, __METHOD__);

        $this->assertInstanceOf(Connection::class, ConnectionManager::getInstance()->get(__METHOD__));
    }

    public function testGetConnections()
    {
        ConnectionManager::getInstance()->add($this->dsn, __METHOD__);

        $this->assertArrayHasKey(__METHOD__, ConnectionManager::getInstance()->all());
        $this->assertTrue(ConnectionManager::getInstance()->has(__METHOD__));
    }

    public function testSetDefaultConnection()
    {
        ConnectionManager::getInstance()->add($this->dsn, __METHOD__);
        ConnectionManager::getInstance()->add($this->dsn, __METHOD__ . '2');
        ConnectionManager::getInstance()->setDefaultConnection(__METHOD__ . '2');

        $this->assertInternalType('array', ConnectionManager::getInstance()->getPrevious());
        $this->assertInstanceOf(Connection::class, ConnectionManager::getInstance()->get());
    }

    public function testExceptionWhenSetUnknownDefaultConnection()
    {
        $this->setExpectedException(DBALException::class);
        ConnectionManager::getInstance()->setDefaultConnection(__METHOD__);
    }

    public function testExceptionWhenGetUnknownConnection()
    {
        $this->setExpectedException(DBALException::class);
        ConnectionManager::getInstance()->get(__METHOD__);
    }

    public function testExceptionWhenSetExistingConnection()
    {
        $this->setExpectedException(DBALException::class);
        ConnectionManager::getInstance()->add($this->dsn, ConnectionManagerInterface::DEFAULT_CONNECTION);
        ConnectionManager::getInstance()->add($this->dsn, ConnectionManagerInterface::DEFAULT_CONNECTION);
    }

    public function testExceptionWhenSetInvalidConnection()
    {
        $this->setExpectedException(DBALException::class);
        ConnectionManager::getInstance()->add(1234, 'invalid');
    }
}
