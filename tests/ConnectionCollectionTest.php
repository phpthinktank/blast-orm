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
        LocatorFacade::getConnectionManager()->__destruct();
    }

    public function testImplementsContainerCollectionInterface()
    {
        $this->assertTrue(is_subclass_of(ConnectionManager::class, ConnectionManagerInterface::class));
    }

    public function testAddConnectionString()
    {
        LocatorFacade::getConnectionManager()->addConnection('sqlite:///:memory:', __METHOD__);
        $this->assertInstanceOf(Connection::class, LocatorFacade::getConnectionManager()->getConnection(__METHOD__));
    }

    public function testAddConnectionArray()
    {
        LocatorFacade::getConnectionManager()->addConnection($this->dsn, __METHOD__);

        $this->assertInstanceOf(Connection::class, LocatorFacade::getConnectionManager()->getConnection(__METHOD__));
    }

    public function testAddConnectionObject()
    {
        $dbalConfiguration = new Configuration();
        $connection = DriverManager::getConnection($this->dsn, $dbalConfiguration);

        LocatorFacade::getConnectionManager()->addConnection($connection, __METHOD__);

        $this->assertInstanceOf(Connection::class, LocatorFacade::getConnectionManager()->getConnection(__METHOD__));
    }

    public function testGetConnections()
    {
        LocatorFacade::getConnectionManager()->addConnection($this->dsn, __METHOD__);

        $this->assertArrayHasKey(__METHOD__, LocatorFacade::getConnectionManager()->getConnections());
        $this->assertTrue(LocatorFacade::getConnectionManager()->hasConnection(__METHOD__));
    }

    public function testSetDefaultConnection()
    {
        LocatorFacade::getConnectionManager()->addConnection($this->dsn, __METHOD__);
        LocatorFacade::getConnectionManager()->addConnection($this->dsn, __METHOD__ . '2');
        LocatorFacade::getConnectionManager()->setDefaultConnection(__METHOD__ . '2');

        $this->assertInternalType('array', LocatorFacade::getConnectionManager()->getPreviousConnections());
        $this->assertInstanceOf(Connection::class, LocatorFacade::getConnectionManager()->getConnection());
    }

    public function testExceptionWhenSetUnknownDefaultConnection()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        LocatorFacade::getConnectionManager()->setDefaultConnection(__METHOD__);
    }

    public function testExceptionWhenGetUnknownConnection()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        LocatorFacade::getConnectionManager()->getConnection(__METHOD__);
    }

    public function testExceptionWhenSetExistingConnection()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        LocatorFacade::getConnectionManager()->addConnection($this->dsn, ConnectionManagerInterface::DEFAULT_CONNECTION);
        LocatorFacade::getConnectionManager()->addConnection($this->dsn, ConnectionManagerInterface::DEFAULT_CONNECTION);
    }

    public function testExceptionWhenSetInvalidConnection()
    {
        $this->setExpectedException(RuntimeException::class);
        LocatorFacade::getConnectionManager()->addConnection(1234, 'invalid');
    }
}
