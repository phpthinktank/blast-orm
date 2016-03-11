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

use Blast\Orm\Data\DataObject;
use Blast\Orm\Entity\EntityAdapter;
use Blast\Orm\Entity\EntityHydratorInterface;
use Blast\Orm\Entity\Definition\Definition;
use Blast\Orm\LocatorFacade;
use Blast\Orm\MapperInterface;
use Blast\Orm\Entity\Entity;
use Blast\Orm\Relations\RelationInterface;
use Blast\Tests\Orm\Stubs\Entities\EntityWithRelation;
use Blast\Tests\Orm\Stubs\Entities\Post;
use stdClass;

class EntityAdapterTest extends \PHPUnit_Framework_TestCase
{

    public function testLoadEntityAdapter()
    {
        $adapter = LocatorFacade::getAdapterManager()->get(Post::class);

        $this->assertInstanceOf(EntityAdapter::class, $adapter);
        $this->assertInstanceOf(Post::class, $adapter->getObject());
    }

    public function testCreateEntity()
    {
        $entity = LocatorFacade::getAdapterManager()->createObject(Post::class);
        $this->assertInstanceOf(Post::class, $entity);
    }

    public function testDecoratorImplementsDataDecorator()
    {
        $this->assertTrue(is_subclass_of(EntityAdapter::class, EntityHydratorInterface::class));
    }

    public function testGetData()
    {
        $adapter = new EntityAdapter();

        $data = $adapter->setData([['name' => 'bob']])->getData();
        $this->assertArrayHasKey('name', array_shift($data));
    }

    public function testGetEntity()
    {
        $this->assertInstanceOf(stdClass::class, LocatorFacade::getAdapterManager()->get(stdClass::class)->getObject());
    }

    public function testHydrateRaw()
    {
        $adapter = new EntityAdapter();

        $this->assertInternalType('array', $adapter->hydrate([['name' => 'bob']], EntityHydratorInterface::HYDRATE_RAW));
    }

    public function testHydrateResult()
    {
        $adapter = new EntityAdapter();

        $this->assertInstanceOf(Entity::class, $adapter->hydrate([['name' => 'bob']], EntityHydratorInterface::HYDRATE_ENTITY));
    }

    public function testHydrateResultWithRelations()
    {

        $adapter = new EntityAdapter(EntityWithRelation::class);

        $relations = $adapter->getRelations();

        $entity = $adapter->hydrate([['name' => 'bob']], EntityHydratorInterface::HYDRATE_ENTITY);
        $this->assertInstanceOf(EntityWithRelation::class, $entity);

        $data = $entity->getData();
        foreach($relations as $relation){
            $this->assertArrayHasKey($relation->getName(), $data);
            $this->assertInstanceOf(RelationInterface::class, $data[$relation->getName()]);
        }
    }

    public function testHydrateGivenEntity()
    {
        $this->assertInstanceOf(stdClass::class, LocatorFacade::getAdapterManager()->get(stdClass::class)->hydrate([['name' => 'bob']], EntityHydratorInterface::HYDRATE_ENTITY));
    }

    public function testHydrateCollection()
    {
        $adapter = new EntityAdapter();
        $this->assertInstanceOf(DataObject::class, $adapter->hydrate([['name' => 'bob']], EntityHydratorInterface::HYDRATE_COLLECTION));
    }

    public function testReceiveComputedData()
    {
        $adapter = new EntityAdapter(new Definition('testTable'));

        $this->assertEquals('testTable', $adapter->getTableName());
        $this->assertEquals('id', $adapter->getPrimaryKeyName());
        $this->assertInstanceOf(MapperInterface::class, $adapter->getMapper());
        $this->assertInternalType('array', $adapter->getRelations());
        $this->assertInternalType('array', $adapter->getFields());
        $this->assertInternalType('array', $adapter->getIndexes());

    }
}
