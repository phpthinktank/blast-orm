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

namespace Blast\Tests\Orm\Query;


use Blast\Orm\ConnectionManagerInterface;
use Blast\Orm\Hydrator\HydratorInterface;
use Blast\Orm\LocatorFacade;
use Blast\Orm\Query;
use Blast\Orm\Query\Events\QueryBuilderEvent;
use Blast\Orm\Query\Events\QueryResultEvent;
use Blast\Orm\Entity\Entity;
use Doctrine\DBAL\Query\QueryBuilder;
use stdClass;

class QueryTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        $connection = LocatorFacade::getConnectionManager()->addConnection( [
            'url' => 'sqlite:///:memory:',
            'memory' => 'true'
        ])->getConnection();

        $connection->exec('CREATE TABLE post (id int, user_id int, title VARCHAR(255), content TEXT)');
        $connection->exec('CREATE TABLE user (id int, name VARCHAR(255))');
        $connection->insert('post', [
            'id' => 1,
            'user_id' => 1,
            'title' => 'Hello World',
            'content' => 'Some text',
        ]);
        $connection->insert('post', [
            'id' => 2,
            'user_id' => 1,
            'title' => 'Next thing',
            'content' => 'More text to read'
        ]);
        $connection->insert('user', [
            'id' => 1,
            'name' => 'Franz'
        ]);
    }

    protected function tearDown()
    {
        $connection = LocatorFacade::getConnectionManager()->getConnection(ConnectionManagerInterface::DEFAULT_CONNECTION);
        $connection->exec('DROP TABLE post');
        $connection->exec('DROP TABLE user');

        LocatorFacade::getConnectionManager()->__destruct();
    }

    /**
     * Assert that result is a DataObject and contains a list of Result objects
     */
    public function testResultCollection()
    {
        $query = new Query();
        $result = $query->select()->from('post')->execute();

        $this->assertInstanceOf(\ArrayObject::class, $result);
        $this->assertInstanceOf(Entity::class, $result->current());
    }

    /**
     * Assert that result is a Result object
     */
    public function testSingleResult()
    {
        $query = new Query();
        $result = $query->select()->from('post')->where('id = 1')->execute();

        $this->assertInstanceOf(Entity::class, $result);
    }

    /**
     * Assert that many results is a Result object
     */
    public function testForceSingleResult()
    {
        $query = new Query();
        $result = $query->select()->from('post')->execute(HydratorInterface::HYDRATE_ENTITY);

        $this->assertInstanceOf(Entity::class, $result);
    }

    /**
     * Assert that single result is a DataObject and contains a list of Result objects
     */
    public function testForceResultCollection()
    {
        $query = new Query();
        $result = $query->select()->from('post')->where('id = 1')->execute(HydratorInterface::HYDRATE_COLLECTION);

        $this->assertInstanceOf(\ArrayObject::class, $result);
        $this->assertInstanceOf(Entity::class, $result->current());
    }

    /**
     * Assert that event is emitted before execution
     */
    public function testBeforeEvent()
    {
        $query = new Query();

        //force entity to be a stdClass
        $query->getEmitter()->addListener('before.select', function (QueryBuilderEvent $event) {
            $event->getBuilder()->setEntity(new \stdClass());
        });

        $result = $query->select()->from('post')->where('id = 1')->execute();

        $query->getEmitter()->removeAllListeners('before.select');

        $this->assertInstanceOf(\stdClass::class, $result);
    }

    /**
     * Assert that event is emitted before execution
     */
    public function testBeforeEventAndCancel()
    {
        $query = new Query();

        //force entity to be a stdClass
        $query->getEmitter()->addListener('before.select', function (QueryBuilderEvent $event) {
            $event->setCanceled(true);
        });

        $result = $query->select()->from('post')->execute();
        $this->assertFalse($result);

        $query->getEmitter()->removeAllListeners('before.select');

    }

    /**
     * Assert that event is emitted before execution
     */
    public function testAfterEvent()
    {
        $query = new Query();

        //add additional value to result set
        $query->getEmitter()->addListener('after.select', function (QueryResultEvent $event, Query $builder) {
            $result = $event->getResult();

            foreach($result as $key => $value){
                $result[$key]['contentSize'] = strlen($value['content']);
            }

            $event->setResult($result);
        });

        $result = $query->select()->from('post')->where('id = 1')->execute();

        $data = $result->getData();

        $this->assertEquals($data['contentSize'], strlen($data['content']));
    }

    /**
     * Assert that event is emitted before execution
     */
    public function testAfterEventAndCancel()
    {
        $query = new Query();

        //force entity to be a stdClass
        $query->getEmitter()->addListener('after.select', function (QueryResultEvent $event) {
            $event->setCanceled(true);
        });

        $result = $query->select()->from('post')->execute();
        $this->assertFalse($result);

        $query->getEmitter()->removeAllListeners('after.select');

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
        $this->assertEquals($query->__call('getType'), $query->getBuilder()->getType());
        $this->assertEquals($query->getType(), $query->getBuilder()->getType());
    }

    /**
     * Assert entity instance
     */
    public function testEntityInstance()
    {
        $query = new Query(new \stdClass());
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
        $builder = LocatorFacade::getConnectionManager()->getConnection()->createQueryBuilder();
        $query = new Query(null, $builder);
        $this->assertEquals($builder, $query->getBuilder());
    }
}
