<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 03.03.2016
 * Time: 12:48
 *
 */

namespace Blast\Tests\Orm\Entity;


use Blast\Orm\Entity\Definition\Definition;
use Blast\Orm\LocatorFacade;
use Blast\Tests\Orm\Stubs\Entities\Post;

class DefinitionTest extends \PHPUnit_Framework_TestCase
{

    public function testAdapterInjection(){

        $definition = new Definition('testTable');
        $adapter = LocatorFacade::getAdapter($definition);

        $this->assertEquals($adapter->getTableName(), $definition->getTableName());
    }

    public function testEntityAsTable(){

        $definition = new Definition(Post::class);
        $adapter = LocatorFacade::getAdapter($definition);

        $this->assertEquals($adapter->getTableName(), 'post');
    }

}
