<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 22.02.2016
 * Time: 13:22
 *
 */

namespace Blast\Tests\Orm;


use Blast\Orm\ConnectionManager;
use Blast\Orm\Entity\Definition;
use Blast\Orm\Hydrator\HydratorInterface;
use Blast\Orm\Query;
use Blast\Orm\Query\Events\QueryBuilderEvent;
use Blast\Orm\Query\Events\QueryResultEvent;
use Blast\Tests\Orm\Stubs\Entities\Post;
use Doctrine\DBAL\Query\QueryBuilder;
use stdClass;

class QueryTest extends AbstractDbTestCase
{

    /**
     * Assert that result is a DataObject and contains a list of Result objects
     */
    public function testResultCollection()
    {
        $query = new Query();
        $result = $query->select()->from('post')->execute();

        $this->assertInstanceOf(\SplStack::class, $result);
        $current = $result->current();
        $this->assertInstanceOf(\ArrayObject::class, $current);
    }

    /**
     * Assert that result is a Result object
     */
    public function testSingleResult()
    {
        $query = new Query();
        $result = $query->select()->from('post')->where('id = 1')->execute();

        $this->assertInstanceOf(\ArrayObject::class, $result);
    }

    /**
     * Assert that many results is a Result object
     */
    public function testForceSingleResult()
    {
        $query = new Query();
        $result = $query->select()->from('post')->execute(HydratorInterface::HYDRATE_ENTITY);

        $this->assertInstanceOf(\ArrayObject::class, $result);
    }

    /**
     * Assert that single result is a DataObject and contains a list of Result objects
     */
    public function testForceResultCollection()
    {
        $query = new Query();
        $result = $query->select()->from('post')->where('id = 1')->execute(HydratorInterface::HYDRATE_COLLECTION);

        $this->assertInstanceOf(\SplStack::class, $result);
        $this->assertInstanceOf(\ArrayObject::class, $result->current());
    }

    /**
     * Assert that event is emitted before execution
     */
    public function testBeforeEvent()
    {
        $query = new Query();

        //force entity to be a stdClass
        $query->getEmitter()->addListener('build.select', function (QueryBuilderEvent $event) {
            $event->getBuilder()->setEntity(Post::class);
        });

        $result = $query->select()->from('post')->where('id = 1')->execute();

        $query->getEmitter()->removeAllListeners('build.select');

        $this->assertInstanceOf(Post::class, $result);
    }

    /**
     * Assert that event is emitted before execution
     */
    public function testBeforeEventAndCancel()
    {
        $query = new Query();

        //force entity to be a stdClass
        $query->getEmitter()->addListener('build.select', function (QueryBuilderEvent $event) {
            $event->setCanceled(true);
        });

        $result = $query->select()->from('post')->execute();
        $this->assertFalse($result);

        $query->getEmitter()->removeAllListeners('build.select');

    }

    /**
     * Assert that event is emitted before execution
     */
    public function testAfterEvent()
    {
        $query = new Query();

        //add additional value to result set
        $query->getEmitter()->addListener('result.select', function (QueryResultEvent $event, Query $builder) {
            $this->assertInstanceOf($builder, Query::class);
            $result = $event->getResult();

            foreach ($result as $key => $value) {
                $result[$key]['contentSize'] = strlen($value['content']);
            }

            $event->setResult($result);
        });

        $result = $query->select()->from('post')->where('id = 1')->execute();

        $data = $result->getArrayCopy();

        $this->assertEquals($data['contentSize'], strlen($data['content']));

        $query->getEmitter()->removeAllListeners('result.select');
    }

    /**
     * Assert that event is emitted before execution
     */
    public function testAfterEventAndCancel()
    {
        $query = new Query();

        //force entity to be a stdClass
        $query->getEmitter()->addListener('result.select', function (QueryResultEvent $event) {
            $event->setCanceled(true);
        });

        $result = $query->select()->from('post')->execute();
        $this->assertFalse($result);

        $query->getEmitter()->removeAllListeners('result.select');

    }

    /**
     * Assert that numeric value of type is returning type name
     */
    public function testQueryTypeName()
    {
        $query = new Query();
        $this->assertEquals('select', $query->select()->getTypeName());
    }

    /**
     * Assert that magically called QueryBuilder methods are equal
     */
    public function testMagicCallQueryBuilderMethods()
    {
        $query = new Query();
        $this->assertEquals($query->getType(), $query->getBuilder()->getType());
    }

    /**
     * Assert entity instance
     */
    public function testEntityInstance()
    {
        $query = new Query(null, new \stdClass());
        $this->assertInstanceOf(\stdClass::class, $query->getEntity());
    }

    /**
     * Assert builder instance
     */
    public function testBuilderInstance()
    {
        $query = new Query();
        $this->assertInstanceOf(QueryBuilder::class, $query->getBuilder());
    }


    /**
     * Assert custom builder instance
     */
    public function testCustomBuilderInstance()
    {
        $connection = ConnectionManager::getInstance()->get();
        $builder = $connection->createQueryBuilder();
        $query = new Query($connection);
        $query->setBuilder($connection->createQueryBuilder());
        $this->assertEquals($builder, $query->getBuilder());
    }


    public function testUseDefinition()
    {
        $definition = new Definition();
        $definition->setConfiguration([
            'tableName' => 'user_role'
        ]);
        $query = new Query(ConnectionManager::getInstance()->get(), $definition);
        $result = $query->select()->from($definition->getTableName())->setMaxResults(1)->execute();

        if (false === $result) {
            $this->markTestIncomplete('This should not return false');

            return;
        }

        if ($result instanceof \SplStack) {
            $this->markTestIncomplete('This should not return \SplStack');

            return;
        }

        $this->assertEquals(1, $result['user_pk']);
        $this->assertEquals(1, $result['role_id']);
    }
}
