<?php
/*
*
* (c) Marco Bunge <marco_bunge@web.de>
*
* For the full copyright and license information, please view the LICENSE.txt
* file that was distributed with this source code.
*
* Date: 19.02.2016
* Time: 13:13
*/

namespace Blast\Tests\Db\Query;


use Blast\Db\Data\DataObject;
use Blast\Db\Query\Query;
use Blast\Db\Query\ResultCollection;

class ResultCollectionTest extends \PHPUnit_Framework_TestCase
{


    public function testQueryAccessor()
    {
        $query = $this->prophesize(Query::class)->reveal();
        $resultCollection = new ResultCollection($query);
        $this->assertEquals($query, $resultCollection->getQuery());
    }

    public function testResultExtendsDataObject()
    {
        $this->assertTrue(is_subclass_of(ResultCollection::class, DataObject::class));
    }
}
