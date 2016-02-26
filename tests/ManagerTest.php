<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 26.11.2015
 * Time: 11:34
 */

namespace Blast\Tests\Orm;


use Blast\Orm\ConnectionCollectionInterface;
use Blast\Orm\Manager;
use Blast\Orm\ManagerInterface;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\DriverManager;
use Interop\Container\ContainerInterface;
use Prophecy\Prophecy\ObjectProphecy;
use InvalidArgumentException;
use RuntimeException;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $container;

    protected $dsn = [
        'url' => 'sqlite:///:memory:',
        'memory' => 'true'
    ];

    protected function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class)->willImplement(ContainerInterface::class);
    }

    public function tearDown()
    {
        if(Manager::isBooted()){
            Manager::shutdown();
        }
    }


    public function testCreate()
    {
        $this->assertFalse(Manager::isBooted());
        $container = $this->container->reveal();
        $manager = Manager::create($container, $this->dsn);
        $this->assertTrue(Manager::isBooted());
        $this->assertInstanceOf(ManagerInterface::class, $manager);
        $this->assertInstanceOf(ContainerInterface::class, $manager->getContainer());

        Manager::shutdown();
        $this->assertFalse(Manager::isBooted());

    }

    public function testGetInstance(){
        $container = $this->container->reveal();
        Manager::create($container, $this->dsn);
        $this->assertInstanceOf(ManagerInterface::class, Manager::getInstance());
    }

    public function testSetContainer(){
        $container = $this->container->reveal();
        $manager = Manager::create($container, $this->dsn);

        $this->assertInstanceOf(ManagerInterface::class, $manager->setContainer($container));
    }

    public function testAddConnectionString()
    {
        $container = $this->container->reveal();
        $manager = Manager::create($container, $this->dsn);
        $manager->addConnection('string', 'sqlite:///:memory:');

        $this->assertInstanceOf(Connection::class, $manager->getConnection('string'));
    }

    public function testAddConnectionArray()
    {
        $container = $this->container->reveal();
        $manager = Manager::create($container, $this->dsn);
        $manager->addConnection('array', $this->dsn);

        $this->assertInstanceOf(Connection::class, $manager->getConnection('array'));
    }

    public function testAddConnectionObject()
    {
        $container = $this->container->reveal();
        $manager = Manager::create($container, $this->dsn);

        $dbalConfiguration = new Configuration();
        $connection = DriverManager::getConnection($this->dsn, $dbalConfiguration);

        $manager->addConnection('object', $connection);

        $this->assertInstanceOf(Connection::class, $manager->getConnection('object'));
    }

    public function testGetConnections()
    {
        $container = $this->container->reveal();
        $manager = Manager::create($container, $this->dsn);

        $manager->addConnection('string', $this->dsn);
        $manager->addConnection('string2', $this->dsn);

        $this->assertArrayHasKey('string', $manager->getConnections());
        $this->assertArrayHasKey('string2', $manager->getConnections());
    }

    public function testSetDefaultConnection()
    {
        $container = $this->container->reveal();
        $manager = Manager::create($container, $this->dsn);

        $manager->addConnection('string', $this->dsn);
        $manager->addConnection('string2', $this->dsn);
        $manager->setDefaultConnection('string2');

        $this->assertInternalType('array', $manager->getPreviousConnections());
        $this->assertInstanceOf(Connection::class, $manager->getConnection());
    }

    public function testExceptionWhenSetUnknownDefaultConnection(){
        $this->setExpectedException(InvalidArgumentException::class);
        $container = $this->container->reveal();
        $manager = Manager::create($container, $this->dsn);
        $manager->setDefaultConnection('string2');
    }

    public function testExceptionWhenGetUnknownConnection(){
        $this->setExpectedException(InvalidArgumentException::class);
        $container = $this->container->reveal();
        $manager = Manager::create($container, $this->dsn);
        $manager->getConnection('string2');
    }

    public function testExceptionWhenSetExistingConnection(){
        $this->setExpectedException(InvalidArgumentException::class);
        $container = $this->container->reveal();
        $manager = Manager::create($container, $this->dsn);
        $manager->addConnection(ConnectionCollectionInterface::DEFAULT_CONNECTION, $this->dsn);
    }

    public function testExceptionWhenSetInvalidConnection(){
        $this->setExpectedException(RuntimeException::class);
        $container = $this->container->reveal();
        $manager = Manager::create($container, $this->dsn);
        $manager->addConnection('invalid', 1234);
    }

    public function testExceptionWhenNotCreated(){
        $this->setExpectedException(RuntimeException::class);
        Manager::getInstance();
    }

    public function testExceptionWhenAlreadyCreated(){
        $this->setExpectedException(RuntimeException::class);
        $container = $this->container->reveal();
        Manager::create($container, $this->dsn);
        Manager::create($container, $this->dsn);
    }
}
