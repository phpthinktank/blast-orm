<?php
/*
*
* (c) Marco Bunge <marco_bunge@web.de>
*
* For the full copyright and license information, please view the LICENSE.txt
* file that was distributed with this source code.
*
* Date: 19.02.2016
* Time: 13:12
*/

namespace Blast\Tests\Orm\Query;


use Blast\Orm\Data\AccessorInterface;
use Blast\Orm\Data\DataObject;
use Blast\Orm\Data\MutatorInterface;
use Blast\Orm\Query\Query;
use Blast\Orm\Entity\Entity;

class ResultTest extends \PHPUnit_Framework_TestCase
{

    public function testResultExtendsDataObject()
    {
        $this->assertTrue(is_subclass_of(Entity::class, DataObject::class));
    }

    public function testResultImplementsMutator()
    {
        $this->assertTrue(is_subclass_of(Entity::class, MutatorInterface::class));
    }

    public function testResultImplementsAccessor()
    {
        $this->assertTrue(is_subclass_of(Entity::class, AccessorInterface::class));
    }


}
