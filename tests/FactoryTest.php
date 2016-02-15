<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 26.11.2015
 * Time: 11:34
 */

namespace Blast\Tests\Db;


use Blast\Db\Configuration;
use Blast\Db\ConfigurationInterface;
use Blast\Db\Manager;
use Blast\Db\ManagerInterface;
use Interop\Container\ContainerInterface;
use Prophecy\Prophecy\ObjectProphecy;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $container;

    protected function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class)->willImplement(ContainerInterface::class);
    }


    public function testCreate()
    {
        $this->assertFalse(Manager::isBooted());
        $container = $this->container->reveal();
        $factory = Manager::create($container, [
            'url' => 'sqlite:///:memory:',
            'memory' => 'true'
        ]);
        $this->assertTrue(Manager::isBooted());
        $this->assertInstanceOf(ManagerInterface::class, $factory);
        $this->assertInstanceOf(ConfigurationInterface::class, $factory->getConfig());
        $this->assertInstanceOf(ContainerInterface::class, $factory->getContainer());

        $factory->shutdown();
        $this->assertFalse(Manager::isBooted());

    }

    public function testSetConfig(){
        $container = $this->container->reveal();
        $factory = Manager::create($container, [
            'url' => 'sqlite:///:memory:',
            'memory' => 'true'
        ]);

        $this->assertInstanceOf(ManagerInterface::class, $factory->setConfig(new Configuration()));
        $factory->shutdown();
    }

    public function testSetContainer(){
        $container = $this->container->reveal();
        $factory = Manager::create($container, [
            'url' => 'sqlite:///:memory:',
            'memory' => 'true'
        ]);

        $this->assertInstanceOf(ManagerInterface::class, $factory->setContainer($container));
        $factory->shutdown();
    }
}
