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
use Blast\Orm\Entity\ProviderInterface;
use Blast\Orm\Locator;
use Blast\Orm\LocatorInterface;
use Blast\Orm\MapperInterface;
use Blast\Tests\Orm\Stubs\Entities\Address;

class LocatorTest extends \PHPUnit_Framework_TestCase
{
    public function testImplementsLocatorInterface()
    {
        $this->assertTrue(is_subclass_of(Locator::class, LocatorInterface::class));
    }

    public function testGetEntityAdapter()
    {
        $locator = new Locator();
        $provider = $locator->getProvider(Address::class);

        $this->assertInstanceOf(ProviderInterface::class, $provider);
        $this->assertEquals(Address::class, get_class($provider->getEntity()));
    }

    public function testGetConnections()
    {
        $locator = new Locator();
        $connections = $locator->getConnectionManager();

        $this->assertInstanceOf(ConnectionManagerInterface::class, $connections);
    }

    public function testGetEntityMapper()
    {
        $locator = new Locator();
        $mapper = $locator->getMapper(Address::class);

        $this->assertInstanceOf(MapperInterface::class, $mapper);
    }


}
