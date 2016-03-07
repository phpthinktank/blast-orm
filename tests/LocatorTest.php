<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 07.03.2016
 * Time: 08:18
 *
 */

namespace Blast\Tests\Orm;


use Blast\Orm\ConnectionManagerInterface;
use Blast\Orm\Entity\EntityAdapter;
use Blast\Orm\Locator;
use Blast\Orm\LocatorFacade;
use Blast\Orm\LocatorInterface;
use Blast\Orm\MapperInterface;
use Blast\Tests\Orm\Stubs\Entities\Address;

class LocatorTest extends \PHPUnit_Framework_TestCase
{
    public function testImplementsLocatorInterface()
    {
        $this->assertTrue(is_subclass_of(Locator::class, LocatorInterface::class));
    }

    public function testGetEntityAdapter(){
        $locator = new Locator();
        $adapter = $locator->getAdapter(Address::class);

        $this->assertInstanceOf(EntityAdapter::class, $adapter);
        $this->assertEquals(Address::class, $adapter->getClassName());
    }

    public function testGetConnections(){
        $locator = new Locator();
        $connections = $locator->getConnectionManager();

        $this->assertInstanceOf(ConnectionManagerInterface::class, $connections);
    }

    public function testGetEntityMapper(){
        $locator = new Locator();
        $mapper = $locator->getMapper(Address::class);

        $this->assertInstanceOf(MapperInterface::class, $mapper);
    }

    public function testGetEntityAdapterFromFacade(){
        $adapter = LocatorFacade::getAdapter(Address::class);

        $this->assertInstanceOf(EntityAdapter::class, $adapter);
        $this->assertEquals(Address::class, $adapter->getClassName());
    }

    public function testGetConnectionsFromFacade(){
        $connections = LocatorFacade::getConnectionManager();

        $this->assertInstanceOf(ConnectionManagerInterface::class, $connections);
    }

    public function testGetEntityMapperFromFacade(){
        $mapper = LocatorFacade::getMapper(Address::class);

        $this->assertInstanceOf(MapperInterface::class, $mapper);
    }

    public function testGetLocatorInstanceFromFacade()
    {
        $this->assertInstanceOf(LocatorInterface::class, LocatorFacade::__instance());
    }


}
