<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 24.02.2016
 * Time: 13:22
 *
 */

namespace Blast\Tests\Orm\Entity;


use Blast\Orm\Data\DataHydratorInterface;
use Blast\Orm\Data\DataObject;
use Blast\Orm\Entity\EntityAdapter;
use Blast\Orm\Entity\EntityAdapterInterface;
use Blast\Orm\Entity\EntityHydratorInterface;
use Blast\Orm\Manager;
use Blast\Orm\Query\Result;
use Interop\Container\ContainerInterface;
use stdClass;

class EntityAdapterTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        $container = $this->prophesize(ContainerInterface::class)->willImplement(ContainerInterface::class)->reveal();
        Manager::create($container, [
            'url' => 'sqlite:///:memory:',
            'memory' => 'true'
        ]);

    }

    protected function tearDown()
    {
        Manager::shutdown();
    }

    public function testLoadEntityAdapter(){

    }

    public function testDecoratorImplementsDataDecorator(){
        $this->assertTrue(is_subclass_of(EntityAdapter::class, EntityHydratorInterface::class));
    }

    public function testGetData(){
        $adapter = new EntityAdapter();

        $data = $adapter->setData([['name' => 'bob']])->getData();
        $this->assertArrayHasKey('name', array_shift($data));
    }

    public function testGetEntity(){
        $this->assertInstanceOf(stdClass::class, EntityAdapter::load(stdClass::class)->getObject());
    }

    public function testDecorateRaw(){
        $adapter = new EntityAdapter();

        $this->assertInternalType('array', $adapter->hydrate([['name' => 'bob']], EntityHydratorInterface::HYDRATE_RAW));
    }

    public function testDecorateGenericEntity(){
        $adapter = new EntityAdapter();

        $this->assertInstanceOf(Result::class, $adapter->hydrate([['name' => 'bob']], EntityHydratorInterface::HYDRATE_ENTITY));
    }

    public function testDecorateGivenEntity(){
        $this->assertInstanceOf(stdClass::class, EntityAdapter::load(stdClass::class)->hydrate([['name' => 'bob']], EntityHydratorInterface::HYDRATE_ENTITY));
    }

    public function testDecorateCollection(){
        $adapter = new EntityAdapter();

        $this->assertInstanceOf(DataObject::class, $adapter->hydrate([['name' => 'bob']], EntityHydratorInterface::HYDRATE_COLLECTION));
    }
}
