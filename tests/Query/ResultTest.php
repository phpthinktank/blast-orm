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

namespace Blast\Tests\Db\Query;


use Blast\Db\Data\AccessorInterface;
use Blast\Db\Data\DataObject;
use Blast\Db\Data\MutatorInterface;
use Blast\Db\Query\Query;
use Blast\Db\Query\Result;

class ResultTest extends \PHPUnit_Framework_TestCase
{

    public function testResultExtendsDataObject()
    {
        $this->assertTrue(is_subclass_of(Result::class, DataObject::class));
    }

    public function testResultImplementsMutator()
    {
        $this->assertTrue(is_subclass_of(Result::class, MutatorInterface::class));
    }

    public function testResultImplementsAccessor()
    {
        $this->assertTrue(is_subclass_of(Result::class, AccessorInterface::class));
    }


}
