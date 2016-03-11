<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 26.11.2015
 * Time: 11:34
 */

namespace Blast\Tests\Orm;


use Blast\Orm\ConnectionManagerInterface;
use Blast\Orm\ConnectionManager;
use Blast\Orm\ConnectionFacade;
use Blast\Orm\LocatorFacade;
use Blast\Orm\ManagerInterface;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\DriverManager;
use Interop\Container\ContainerInterface;
use Prophecy\Prophecy\ObjectProphecy;
use InvalidArgumentException;
use RuntimeException;

class ConnectionCollectionTest extends \PHPUnit_Framework_TestCase
{
    protected $dsn = [
        'url' => 'sqlite:///:memory:',
        'memory' => 'true'
    ];

    public function tearDown()
    {
        LocatorFacade::getConnectionManager()->closeAll();
    }

    public function testImplementsContainerCollectionInterface()
    {
        $this->assertTrue(is_subclass_of(ConnectionManager::class, ConnectionManagerInterface::class));
    }

    public function testAddConnectionString()
    {
        LocatorFacade::getConnectionManager()->add('sqlite:///:memory:', __METHOD__);
        $this->assertInstanceOf(Connection::class, LocatorFacade::getConnectionManager()->get(__METHOD__));
    }

    public function testAddConnectionArray()
    {
        LocatorFacade::getConnectionManager()->add($this->dsn, __METHOD__);

        $this->assertInstanceOf(Connection::class, LocatorFacade::getConnectionManager()->get(__METHOD__));
    }

    public function testAddConnectionObject()
    {
        $dbalConfiguration = new Configuration();
        $connection = DriverManager::getConnection($this->dsn, $dbalConfiguration);

        LocatorFacade::getConnectionManager()->add($connection, __METHOD__);

        $this->assertInstanceOf(Connection::class, LocatorFacade::getConnectionManager()->get(__METHOD__));
    }

    public function testGetConnections()
    {
        LocatorFacade::getConnectionManager()->add($this->dsn, __METHOD__);

        $this->assertArrayHasKey(__METHOD__, LocatorFacade::getConnectionManager()->all());
        $this->assertTrue(LocatorFacade::getConnectionManager()->has(__METHOD__));
    }

    public function testSetDefaultConnection()
    {
        LocatorFacade::getConnectionManager()->add($this->dsn, __METHOD__);
        LocatorFacade::getConnectionManager()->add($this->dsn, __METHOD__ . '2');
        LocatorFacade::getConnectionManager()->setDefaultConnection(__METHOD__ . '2');

        $this->assertInternalType('array', LocatorFacade::getConnectionManager()->getPrevious());
        $this->assertInstanceOf(Connection::class, LocatorFacade::getConnectionManager()->get());
    }

    public function testExceptionWhenSetUnknownDefaultConnection()
    {
        $this->setExpectedException(DBALException::class);
        LocatorFacade::getConnectionManager()->setDefaultConnection(__METHOD__);
    }

    public function testExceptionWhenGetUnknownConnection()
    {
        $this->setExpectedException(DBALException::class);
        LocatorFacade::getConnectionManager()->get(__METHOD__);
    }

    public function testExceptionWhenSetExistingConnection()
    {
        $this->setExpectedException(DBALException::class);
        LocatorFacade::getConnectionManager()->add($this->dsn, ConnectionManagerInterface::DEFAULT_CONNECTION);
        LocatorFacade::getConnectionManager()->add($this->dsn, ConnectionManagerInterface::DEFAULT_CONNECTION);
    }

    public function testExceptionWhenSetInvalidConnection()
    {
        $this->setExpectedException(DBALException::class);
        LocatorFacade::getConnectionManager()->add(1234, 'invalid');
    }
}
