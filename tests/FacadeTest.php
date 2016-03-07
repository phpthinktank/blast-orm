<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 07.03.2016
 * Time: 12:35
 *
 */

namespace Blast\Tests\Orm;


use Blast\Orm\Facades\FacadeFactory;
use Blast\Tests\Orm\Stubs\ContainerIntegration;
use League\Container\ContainerInterface;
class FacadeTest extends \PHPUnit_Framework_TestCase
{
    public function testLeagueContainer()
    {
        $this->assertInstanceOf(ContainerInterface::class, FacadeFactory::getContainer());
    }

    public function testDelegateContainer()
    {
        FacadeFactory::setContainer(new ContainerIntegration());
        $this->assertTrue(FacadeFactory::getContainer()->hasInDelegate('INTEGRATION_TEST'));
    }
}
