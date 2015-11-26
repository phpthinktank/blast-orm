<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 26.11.2015
 * Time: 16:09
 */

namespace Blast\Tests\Orm;


use Blast\Orm\Config;
use Blast\Orm\Entity\EntityInterface;
use Blast\Orm\Factory;
use Blast\Orm\Mapper;
use Interop\Container\ContainerInterface;
use Mocks\AnyEntity;
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

    protected function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class)->willImplement(ContainerInterface::class);
        $this->entity = new AnyEntity();

        $container = $this->container->reveal();
        $factory = Factory::create($container, [
            'url' => 'sqlite:///:memory:',
            'memory' => 'true'
        ]);

        $connection = $factory->getConfig()->getConnection(Factory::DEFAULT_CONNECTION);
        $connection->prepare('CREATE TABLE test (id int, pk int)')->execute();
        $connection->insert('test', [
            'id' => 1,
            'pk' => 1
        ]);
        $connection->insert('test', [
            'id' => 2,
            'pk' => 2
        ]);

        var_dump($connection->fetchAll('select * from test'));
    }

    public function testCreatingMapper()
    {
        $entity = $this->entity;
        $mapper = new Mapper($entity);

        var_dump($mapper->getConnection()->fetchAll('select * from test where pk = 1'));

        var_dump($mapper->findBy('pk', 1));
    }
}
