<?php
/*
*
* (c) Marco Bunge <marco_bunge@web.de>
*
* For the full copyright and license information, please view the LICENSE.txt
* file that was distributed with this source code.
*
* Date: 19.02.2016
* Time: 13:28
*/

namespace Query;


use Blast\Db\Data\DataDecoratorInterface;
use Blast\Db\Query\ResultDataDecorator;
use stdClass;

class ResultDataDecoratorTest extends \PHPUnit_Framework_TestCase
{

    public function testDecoratorImplementsDataDecorator(){
        $this->assertTrue(is_subclass_of(ResultDataDecorator::class, DataDecoratorInterface::class));
    }

    public function testGetData(){
        $decorator = new ResultDataDecorator(['name' => 'bob']);

        $this->assertArrayHasKey('name', $decorator->getData());
    }

    public function testGetEntity(){
        $decorator = new ResultDataDecorator([], new stdClass());

        $this->assertInstanceOf(stdClass::class, $decorator->getEntity());
    }

}
