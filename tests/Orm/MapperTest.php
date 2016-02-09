<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 26.11.2015
 * Time: 16:09
 */

namespace Blast\Tests\Db\Orm;

use Blast\Db\ConfigInterface;
use Blast\Db\Entity\CollectionInterface;
use Blast\Db\Entity\EntityInterface;
use Blast\Db\Factory;
use Blast\Db\Orm\Mapper;
use Blast\Tests\Db\Entities\AnyEntity;
use Interop\Container\ContainerInterface;
use Prophecy\Prophecy\ObjectProphecy;

class MapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityInterface
     */
    private $entity;

    /**
     * @var ObjectProphecy
     */
    private $container;

    /**
     * @var Factory
     */
    private $factory;

    protected function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class)->willImplement(ContainerInterface::class);

        $container = $this->container->reveal();
        $factory = Factory::create($container, [
            'url' => 'sqlite:///:memory:',
            'memory' => 'true'
        ]);

        $this->entity = new AnyEntity();

        $connection = $factory->getConfig()->getConnection(ConfigInterface::DEFAULT_CONNECTION);
        $connection->prepare('CREATE TABLE test (id int, pk int, same int)')->execute();
        $connection->insert('test', [
            'id' => 1,
            'pk' => 1,
            'same' => 42
        ]);
        $connection->insert('test', [
            'id' => 2,
            'pk' => 2,
            'same' => 42
        ]);
    }

    protected function tearDown()
    {
        $factory = Factory::getInstance();
        $connection = $factory->getConfig()->getConnection(ConfigInterface::DEFAULT_CONNECTION);
//        $connection->prepare('DROP TABLE test')->execute();
        $factory->shutdown();
    }

    /**
     * select any field
     */
    public function testSelect()
    {
        $entity = $this->entity;
        $mapper = new Mapper($entity);

        $query = $mapper->select();
        $result = $query->where('same = 42')->execute();

        $this->assertInstanceOf(CollectionInterface::class, $result);
        $this->assertEquals(2, $result->count());
    }

    /**
     * find by pk
     */
    public function testFind()
    {
        $entity = $this->entity;
        $mapper = new Mapper($entity);

        $result = $mapper->find(1);

        $this->assertInstanceOf(EntityInterface::class, $result);
    }

    public function testCreateByEntity(){
        $entity = $this->entity;
        $mapper = new Mapper($entity);

        $any = new AnyEntity();
        $any->pk = 1;
        $any->same = 42;

        $result = $mapper->create($any);

        $this->assertTrue(is_numeric($result) && !is_bool($result));
    }
}
