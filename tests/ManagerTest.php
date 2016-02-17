<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 26.11.2015
 * Time: 11:34
 */

namespace Blast\Tests\Db;


use Blast\Db\ConfigurationTrait;
use Blast\Db\ConfigurationInterface;
use Blast\Db\Manager;
use Blast\Db\ManagerInterface;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\DriverManager;
use Interop\Container\ContainerInterface;
use Prophecy\Prophecy\ObjectProphecy;

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

    public function testSetContainer(){
        $container = $this->container->reveal();
        $manager = Manager::create($container, $this->dsn);

        $this->assertInstanceOf(ManagerInterface::class, $manager->setContainer($container));
        Manager::shutdown();
    }

    public function testAddConnectionString()
    {
        $container = $this->container->reveal();
        $manager = Manager::create($container, $this->dsn);
        $manager->addConnection('string', 'sqlite:///:memory:');

        $this->assertInstanceOf(Connection::class, $manager->getConnection('string'));
        Manager::shutdown();
    }

    public function testAddConnectionArray()
    {
        $container = $this->container->reveal();
        $manager = Manager::create($container, $this->dsn);
        $manager->addConnection('array', $this->dsn);

        $this->assertInstanceOf(Connection::class, $manager->getConnection('array'));
        Manager::shutdown();
    }

    public function testAddConnectionObject()
    {
        $container = $this->container->reveal();
        $manager = Manager::create($container, $this->dsn);

        $dbalConfiguration = new Configuration();
        $connection = DriverManager::getConnection($this->dsn, $dbalConfiguration);

        $manager->addConnection('object', $connection);

        $this->assertInstanceOf(Connection::class, $manager->getConnection('object'));
        Manager::shutdown();
    }

    public function testGetConnections()
    {
        $container = $this->container->reveal();
        $manager = Manager::create($container, $this->dsn);

        $manager->addConnection('string', $this->dsn);
        $manager->addConnection('string2', $this->dsn);

        $this->assertArrayHasKey('string', $manager->getConnections());
        $this->assertArrayHasKey('string2', $manager->getConnections());
        Manager::shutdown();
    }
}
