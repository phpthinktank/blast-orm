<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 26.11.2015
 * Time: 11:34
 */

namespace Blast\Tests\Orm;


use Blast\Db\Config;
use Blast\Db\ConfigInterface;
use Blast\Db\Orm\Factory;
use Blast\Db\Orm\FactoryInterface;
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
        $this->assertFalse(Factory::isBooted());
        $container = $this->container->reveal();
        $factory = Factory::create($container, [
            'url' => 'sqlite:///:memory:',
            'memory' => 'true'
        ]);
        $this->assertTrue(Factory::isBooted());
        $this->assertInstanceOf(FactoryInterface::class, $factory);
        $this->assertInstanceOf(ConfigInterface::class, $factory->getConfig());
        $this->assertInstanceOf(ContainerInterface::class, $factory->getContainer());

        $factory->shutdown();
        $this->assertFalse(Factory::isBooted());

    }

    public function testSetConfig(){
        $container = $this->container->reveal();
        $factory = Factory::create($container, [
            'url' => 'sqlite:///:memory:',
            'memory' => 'true'
        ]);

        $this->assertInstanceOf(FactoryInterface::class, $factory->setConfig(new Config()));
        $factory->shutdown();
    }

    public function testSetContainer(){
        $container = $this->container->reveal();
        $factory = Factory::create($container, [
            'url' => 'sqlite:///:memory:',
            'memory' => 'true'
        ]);

        $this->assertInstanceOf(FactoryInterface::class, $factory->setContainer($container));
        $factory->shutdown();
    }
}
