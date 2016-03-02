<?php
/*
*
* (c) Marco Bunge <marco_bunge@web.de>
*
* For the full copyright and license information, please view the LICENSE.txt
* file that was distributed with this source code.
*
* Date: 02.03.2016
* Time: 20:40
*/

namespace Container;


use Blast\Orm\Container\ContainerException;
use Blast\Orm\Container\Definition;
use Blast\Orm\Container\DefinitionInterface;

class DefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function testImplementsDefinitionInterface()
    {
        $this->assertTrue(is_subclass_of(Definition::class, DefinitionInterface::class));
    }

    public function testThrowExceptionConstructDefinition(){
        $this->setExpectedException(ContainerException::class);

        new Definition([], '');
    }
}
