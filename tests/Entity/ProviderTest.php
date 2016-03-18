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

use Blast\Orm\ConnectionManager;
use Blast\Orm\Entity\Definition;
use Blast\Orm\Entity\Provider;
use Blast\Orm\Relations\HasOne;
use Blast\Orm\Relations\RelationInterface;
use Blast\Tests\Orm\Stubs\Entities\Post;
use Blast\Tests\Orm\Stubs\Entities\PostWithUserRelation;
use Blast\Tests\Orm\Stubs\Entities\Role;
use Blast\Tests\Orm\Stubs\Entities\User;

class ProviderTest extends \PHPUnit_Framework_TestCase
{
    protected $dsn = [
        'url' => 'sqlite:///:memory:',
        'memory' => 'true'
    ];

    public function tearDown()
    {
        ConnectionManager::getInstance()->closeAll();
    }

    public function testProvideEntityByTableName()
    {
        $provider = new Provider('testTable');
        $this->assertEquals('testTable', $provider->getTableName());
    }

    public function testProvideEntityByClassName()
    {
        $provider = new Provider(Post::class);
        $this->assertEquals('post', $provider->getTableName());
    }

    public function testProvideEntityByClassNameAndGetTable()
    {
        $provider = new Provider(PostWithUserRelation::class);
        $this->assertEquals('post', $provider->getTableName());
    }

    public function testProvideEntityByObject()
    {
        $provider = new Provider(new Post);
        $this->assertEquals('post', $provider->getTableName());
    }

    public function testProvideEntityByIoCContainer()
    {
        $provider = new Provider('post');
        $this->assertEquals('post', $provider->getTableName());
    }

    public function testProvideEntityByArray()
    {
        $provider = new Provider([
            'tableName' => 'post'
        ]);
        $this->assertEquals('post', $provider->getTableName());
    }

    public function testProvideRelation()
    {
        $provider = new Provider([
            'tableName' => 'post',
            'relations' => function ($entity, $mapper) {
                $this->assertInternalType('object', $entity);
                return [
                    'other' => new HasOne($entity, 'otherTable')
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
        $this->assertEquals('id', (new Provider(Post::class))->getPrimaryKeyName());
        $this->assertEquals('pk', (new Provider(User::class))->getPrimaryKeyName());
    }

    public function testAddDefinition()
    {
        $definition = new Definition();
        $definition->setConfiguration([
            'entity' => Role::class,
            'tableName' => 'my_role'
        ]);

        $provider = new Provider($definition);

        $this->assertEquals('my_role', $provider->getTableName());
        $this->assertInstanceOf(Role::class, $provider->getEntity());
        $this->assertInstanceOf(\SplStack::class, $provider->getDefinition()->getEntityCollection());
    }


}
