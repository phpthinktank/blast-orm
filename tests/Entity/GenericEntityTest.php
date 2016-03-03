<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 03.03.2016
 * Time: 12:48
 *
 */

namespace Blast\Tests\Orm\Entity;


use Blast\Orm\Entity\GenericEntity;
use Blast\Orm\MapperInterface;

class GenericEntityTest extends \PHPUnit_Framework_TestCase
{

    public function testReceiveComputedData(){
        $entity = new GenericEntity('testTable');

        $this->assertEquals('testTable', $entity->getTableName());
        $this->assertEquals('id', $entity->getPrimaryKeyName());
        $this->assertInstanceOf(MapperInterface::class, $entity->getMapper());
        $this->assertInternalType('array', $entity->getFields());
        $this->assertInternalType('array', $entity->getIndexes());

    }

}
