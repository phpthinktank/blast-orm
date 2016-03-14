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
use Blast\Orm\Facades\FacadeFactory;
use Blast\Orm\Relations\HasOne;
use Blast\Orm\Relations\RelationInterface;
use Blast\Tests\Orm\Stubs\Entities\Post;
use Blast\Tests\Orm\Stubs\Entities\User;

class ProviderTest extends \PHPUnit_Framework_TestCase
{

    public function testProvideEntityByTableName()
    {
        $provider = new Provider('testTable');
        $this->assertEquals('testTable', $provider->getTableName());
    }

    public function testProvideEntityByClassName(){
        $provider = new Provider(Post::class);
        $this->assertEquals('post', $provider->getTableName());
    }

    public function testProvideEntityByObject(){
        $provider = new Provider(new Post);
        $this->assertEquals('post', $provider->getTableName());
    }

    public function testProvideEntityByIoCContainer(){
        FacadeFactory::getContainer()->add('post', Post::class);
        $provider = new Provider('post');
        $this->assertEquals('post', $provider->getTableName());
    }

    public function testProvideEntityByArray(){
        $provider = new Provider([
            'tableName' => 'post'
        ]);
        $this->assertEquals('post', $provider->getTableName());
    }

    public function testProvideRelation(){
        $provider = new Provider([
            'tableName' => 'post',
            'relations' => function($entity, $mapper){
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

    public function testProvidePrimaryKeyField(){
        $this->assertEquals('id', (new Provider(Post::class))->getPrimaryKeyName());
        $this->assertEquals('pk', (new Provider(User::class))->getPrimaryKeyName());
    }
}
