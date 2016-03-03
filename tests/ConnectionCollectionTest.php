<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 26.11.2015
 * Time: 11:34
 */

namespace Blast\Tests\Orm;


use Blast\Orm\ConnectionCollectionInterface;
use Blast\Orm\ConnectionCollection;
use Blast\Orm\ConnectionFacade;
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
        ConnectionFacade::__destruct();
    }

    public function testImplementsContainerCollectionInterface()
    {
        $this->assertTrue(is_subclass_of(ConnectionCollection::class, ConnectionCollectionInterface::class));
    }

    public function testAddConnectionString()
    {
        ConnectionFacade::addConnection('sqlite:///:memory:', __METHOD__);
        $this->assertInstanceOf(Connection::class, ConnectionFacade::getConnection(__METHOD__));
    }

    public function testAddConnectionArray()
    {
        ConnectionFacade::addConnection($this->dsn, __METHOD__);

        $this->assertInstanceOf(Connection::class, ConnectionFacade::getConnection(__METHOD__));
    }

    public function testAddConnectionObject()
    {
        $dbalConfiguration = new Configuration();
        $connection = DriverManager::getConnection($this->dsn, $dbalConfiguration);

        ConnectionFacade::addConnection($connection, __METHOD__);

        $this->assertInstanceOf(Connection::class, ConnectionFacade::getConnection(__METHOD__));
    }

    public function testGetConnections()
    {
        ConnectionFacade::addConnection($this->dsn, __METHOD__);

        $this->assertArrayHasKey(__METHOD__, ConnectionFacade::getConnections());
        $this->assertTrue(ConnectionFacade::hasConnection(__METHOD__));
    }

    public function testSetDefaultConnection()
    {
        ConnectionFacade::addConnection($this->dsn, __METHOD__);
        ConnectionFacade::addConnection($this->dsn, __METHOD__ . '2');
        ConnectionFacade::setDefaultConnection(__METHOD__ . '2');

        $this->assertInternalType('array', ConnectionFacade::getPreviousConnections());
        $this->assertInstanceOf(Connection::class, ConnectionFacade::getConnection());
    }

    public function testExceptionWhenSetUnknownDefaultConnection()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        ConnectionFacade::setDefaultConnection(__METHOD__);
    }

    public function testExceptionWhenGetUnknownConnection()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        ConnectionFacade::getConnection(__METHOD__);
    }

    public function testExceptionWhenSetExistingConnection()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        ConnectionFacade::addConnection($this->dsn, ConnectionCollectionInterface::DEFAULT_CONNECTION);
        ConnectionFacade::addConnection($this->dsn, ConnectionCollectionInterface::DEFAULT_CONNECTION);
    }

    public function testExceptionWhenSetInvalidConnection()
    {
        $this->setExpectedException(RuntimeException::class);
        ConnectionFacade::addConnection(1234, 'invalid');
    }
}
