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

namespace Blast\Tests\Orm\Query;


use Blast\Orm\Data\DataDecoratorInterface;
use Blast\Orm\Data\DataObject;
use Blast\Orm\Query\Query;
use Blast\Orm\Query\Result;
use Blast\Orm\Query\ResultCollection;
use Blast\Orm\Query\ResultDataDecorator;
use stdClass;

class ResultDataDecoratorTest extends \PHPUnit_Framework_TestCase
{

    public function testDecoratorImplementsDataDecorator(){
        $this->assertTrue(is_subclass_of(ResultDataDecorator::class, DataDecoratorInterface::class));
    }

    public function testGetData(){
        $decorator = new ResultDataDecorator([['name' => 'bob']]);

        $data = $decorator->getData();
        $this->assertArrayHasKey('name', array_shift($data));
    }

    public function testGetEntity(){
        $decorator = new ResultDataDecorator([], new stdClass());

        $this->assertInstanceOf(stdClass::class, $decorator->getEntity());
    }
    
    public function testDecorateRaw(){
        $decorator = new ResultDataDecorator([['name' => 'bob']]);
        
        $this->assertInternalType('array', $decorator->decorate(ResultDataDecorator::RAW));
    }

    public function testDecorateGenericEntity(){
        $decorator = new ResultDataDecorator([['name' => 'bob']]);

        $this->assertInstanceOf(Result::class, $decorator->decorate(ResultDataDecorator::RESULT_ENTITY));
    }

    public function testDecorateGivenEntity(){
        $decorator = new ResultDataDecorator([['name' => 'bob']], new stdClass());

        $this->assertInstanceOf(stdClass::class, $decorator->decorate(ResultDataDecorator::RESULT_ENTITY));
    }

    public function testDecorateCollection(){
        $decorator = new ResultDataDecorator([['name' => 'bob']]);

        $this->assertInstanceOf(DataObject::class, $decorator->decorate(ResultDataDecorator::RESULT_COLLECTION));
    }

}
