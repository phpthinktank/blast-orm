<?php
/*
*
* (c) Marco Bunge <marco_bunge@web.de>
*
* For the full copyright and license information, please view the LICENSE.txt
* file that was distributed with this source code.
*
* Date: 02.03.2016
* Time: 19:33
*/

namespace Blast\Tests\Orm\Container;


use Blast\Orm\Container\Container;
use Blast\Orm\Container\ContainerException;
use Blast\Orm\Container\DefinitionException;
use Blast\Orm\Container\DefinitionNotFoundException;
use Blast\Tests\Orm\Stubs\Container\ContractByExtension;
use Blast\Tests\Orm\Stubs\Container\ContractByInterface;
use Blast\Tests\Orm\Stubs\Container\ContractClass;
use Blast\Tests\Orm\Stubs\Container\ContractInterface;
use Blast\Tests\Orm\Stubs\Container\NoContract;
use Interop\Container\ContainerInterface;

class ContainerTest extends \PHPUnit_Framework_TestCase
{

    public function testImplementsInteropContainerInterface()
    {
        $this->assertTrue(is_subclass_of(Container::class, ContainerInterface::class));
    }

    public function testAddIdAsClassName(){
        $container = new Container();
        $container->add(ContractClass::class);

        $this->assertInstanceOf(ContractClass::class, $container->get(ContractClass::class));
    }

    public function testAddIdAsObject(){
        $container = new Container();
        $container->add(new ContractClass);

        $this->assertInstanceOf(ContractClass::class, $container->get(ContractClass::class));
    }

    public function testAddIdAsContractClassAndService(){
        $container = new Container();
        $container->add(ContractClass::class, ContractByExtension::class);

        $this->assertInstanceOf(ContractClass::class, $container->get(ContractClass::class));
    }

    public function testAddIdAsContractInterfaceAndService(){
        $container = new Container();
        $container->add(ContractInterface::class, ContractByInterface::class);

        $this->assertInstanceOf(ContractInterface::class, $container->get(ContractInterface::class));
    }

    public function testAddIdAsNameAndService(){
        $container = new Container();
        $container->add('simple.string', ContractClass::class);

        $this->assertInstanceOf(ContractClass::class, $container->get('simple.string'));
    }

    public function testHasService(){
        $container = new Container();
        $container->add(ContractClass::class);

        $this->assertTrue($container->has(ContractClass::class));
    }

    public function testGetAndAddUnknownServiceByClassName(){
        $container = new Container();

        $this->assertInstanceOf(ContractClass::class, $container->get(ContractClass::class));
        $this->assertTrue($container->has(ContractClass::class));
    }

    public function testGetAndAddUnknownServiceByInstance(){
        $container = new Container();

        $this->assertInstanceOf(ContractClass::class, $container->get(new ContractClass()));
        $this->assertTrue($container->has(ContractClass::class));
    }

    public function testThrowExceptionForIllegalId(){
        $this->setExpectedException(ContainerException::class);

        (new Container())->get(['array']);
    }

    public function testThrowExceptionForUnknownDefinition(){
        $this->setExpectedException(DefinitionNotFoundException::class);

        (new Container())->getDefinition('foo');
    }

    public function testThrowExceptionForInvalidInterfaceContract(){
        $this->setExpectedException(DefinitionException::class);

        $container = new Container();

        $container->add(ContractInterface::class, ContractClass::class);
        $container->get(ContractInterface::class);
    }

    public function testThrowExceptionForInvalidClassContract(){
        $this->setExpectedException(DefinitionException::class);

        $container = new Container();

        $container->add(ContractClass::class, NoContract::class);
        $container->get(ContractClass::class);
    }
}
