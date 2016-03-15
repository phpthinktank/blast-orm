<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 26.11.2015
 * Time: 11:34
 */

namespace Blast\Tests\Orm;


use Blast\Orm\ConnectionFacade;
use Blast\Orm\ConnectionManager;
use Blast\Orm\ConnectionManagerInterface;
use Blast\Orm\Locator;
use Blast\Orm\LocatorInterface;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\DriverManager;

class ConnectionCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LocatorInterface
     */
    public $locator;

    protected $dsn = [
        'url' => 'sqlite:///:memory:',
        'memory' => 'true'
    ];

    protected function setUp()
    {
        $this->locator = new Locator();
    }

    public function tearDown()
    {
        $this->locator->getConnectionManager()->closeAll();
    }

    public function testImplementsContainerCollectionInterface()
    {
        $this->assertTrue(is_subclass_of(ConnectionManager::class, ConnectionManagerInterface::class));
    }

    public function testAddConnectionString()
    {
        $this->locator->getConnectionManager()->add('sqlite:///:memory:', __METHOD__);
        $this->assertInstanceOf(Connection::class, $this->locator->getConnectionManager()->get(__METHOD__));
    }

    public function testAddConnectionArray()
    {
        $this->locator->getConnectionManager()->add($this->dsn, __METHOD__);

        $this->assertInstanceOf(Connection::class, $this->locator->getConnectionManager()->get(__METHOD__));
    }

    public function testAddConnectionObject()
    {
        $dbalConfiguration = new Configuration();
        $connection = DriverManager::getConnection($this->dsn, $dbalConfiguration);

        $this->locator->getConnectionManager()->add($connection, __METHOD__);

        $this->assertInstanceOf(Connection::class, $this->locator->getConnectionManager()->get(__METHOD__));
    }

    public function testGetConnections()
    {
        $this->locator->getConnectionManager()->add($this->dsn, __METHOD__);

        $this->assertArrayHasKey(__METHOD__, $this->locator->getConnectionManager()->all());
        $this->assertTrue($this->locator->getConnectionManager()->has(__METHOD__));
    }

    public function testSetDefaultConnection()
    {
        $this->locator->getConnectionManager()->add($this->dsn, __METHOD__);
        $this->locator->getConnectionManager()->add($this->dsn, __METHOD__ . '2');
        $this->locator->getConnectionManager()->setDefaultConnection(__METHOD__ . '2');

        $this->assertInternalType('array', $this->locator->getConnectionManager()->getPrevious());
        $this->assertInstanceOf(Connection::class, $this->locator->getConnectionManager()->get());
    }

    public function testExceptionWhenSetUnknownDefaultConnection()
    {
        $this->setExpectedException(DBALException::class);
        $this->locator->getConnectionManager()->setDefaultConnection(__METHOD__);
    }

    public function testExceptionWhenGetUnknownConnection()
    {
        $this->setExpectedException(DBALException::class);
        $this->locator->getConnectionManager()->get(__METHOD__);
    }

    public function testExceptionWhenSetExistingConnection()
    {
        $this->setExpectedException(DBALException::class);
        $this->locator->getConnectionManager()->add($this->dsn, ConnectionManagerInterface::DEFAULT_CONNECTION);
        $this->locator->getConnectionManager()->add($this->dsn, ConnectionManagerInterface::DEFAULT_CONNECTION);
    }

    public function testExceptionWhenSetInvalidConnection()
    {
        $this->setExpectedException(DBALException::class);
        $this->locator->getConnectionManager()->add(1234, 'invalid');
    }
}
