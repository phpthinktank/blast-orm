<?php
/*
*
* (c) Marco Bunge <marco_bunge@web.de>
*
* For the full copyright and license information, please view the LICENSE.txt
* file that was distributed with this source code.
*
* Date: 11.03.2016
* Time: 19:46
*/

namespace Blast\Tests\Orm\Entity;

use Blast\Orm\Entity\Provider;
use Blast\Orm\Locator;
use Blast\Orm\LocatorInterface;
use Blast\Orm\Relations\HasOne;
use Blast\Orm\Relations\RelationInterface;
use Blast\Tests\Orm\Stubs\Entities\Post;
use Blast\Tests\Orm\Stubs\Entities\User;

class ProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LocatorInterface
     */
    public $locator;

    protected $dsn = [
        'url' => 'sqlite:///:memory:',
        'memory' => 'true'
    ];

    protected function setUp()
    {
        $this->locator = new Locator();
    }

    public function tearDown()
    {
        $this->locator->getConnectionManager()->closeAll();
    }

    public function testProvideEntityByTableName()
    {
        $provider = new Provider($this->locator, 'testTable');
        $this->assertEquals('testTable', $provider->getTableName());
    }

    public function testProvideEntityByClassName()
    {
        $provider = new Provider($this->locator, Post::class);
        $this->assertEquals('post', $provider->getTableName());
    }

    public function testProvideEntityByObject()
    {
        $provider = new Provider($this->locator, new Post);
        $this->assertEquals('post', $provider->getTableName());
    }

    public function testProvideEntityByIoCContainer()
    {
        $this->locator->getContainer()->add('post', Post::class);
        $provider = new Provider($this->locator, 'post');
        $this->assertEquals('post', $provider->getTableName());
    }

    public function testProvideEntityByArray()
    {
        $provider = new Provider($this->locator, [
            'tableName' => 'post'
        ]);
        $this->assertEquals('post', $provider->getTableName());
    }

    public function testProvideRelation()
    {
        $provider = new Provider($this->locator, [
            'tableName' => 'post',
            'relations' => function ($entity, $mapper) {
                $this->assertInternalType('object', $entity);
                return [
                    'other' => new HasOne($this->locator, $entity, 'otherTable')
                ];
            },
        ]);
        $this->assertEquals('post', $provider->getTableName());
        $relations = $provider->getRelations();
        $this->assertInternalType('array', $relations);
        $relation = array_shift($relations);
        $this->assertInstanceOf(RelationInterface::class, $relation);
    }

    public function testProvidePrimaryKeyField()
    {
        $this->assertEquals('id', (new Provider($this->locator, Post::class))->getPrimaryKeyName());
        $this->assertEquals('pk', (new Provider($this->locator, User::class))->getPrimaryKeyName());
    }
}
