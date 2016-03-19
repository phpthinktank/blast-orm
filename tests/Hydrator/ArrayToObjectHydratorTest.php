<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 18.03.2016
 * Time: 15:57
 *
 */

namespace Blast\Tests\Orm\Hydrator;


use Blast\Orm\Entity\Provider;
use Blast\Orm\Relations\RelationInterface;
use Blast\Tests\Orm\Stubs\Entities\PostWithUserRelation;
use Blast\Tests\Orm\Stubs\Entities\User;

class ArrayToObjectHydratorTest extends \PHPUnit_Framework_TestCase
{


    public function testHydrateToPlainObject()
    {
        $entity = User::class;
        $data = [
            'pk' => 1,
            'name' => 'Gunther'
        ];
        $provider = new Provider($entity);

        $entityClone = $provider->withData($data);

        $this->assertEquals($data['pk'], $entityClone->getPk());
        $this->assertEquals($data['name'], $entityClone->getName());

    }

    public function testHydrateToRelation()
    {
        $entity = PostWithUserRelation::class;
        $data = [
            'id' => 1,
            'title' => 'Hello',
            'user_pk' => 1
        ];
        $provider = new Provider($entity);

        $entityClone = $provider->withData($data);
        $cp = $entityClone->getArrayCopy();

        $this->assertEquals($data['id'], $entityClone['id']);
        $this->assertEquals($data['title'], $entityClone['title']);
//        $this->assertInstanceOf(RelationInterface::class, $entityClone['users']);
//        $this->assertInstanceOf(RelationInterface::class, $entityClone['user_relation']);

    }
}
