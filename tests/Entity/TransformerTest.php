<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 17.03.2016
 * Time: 09:23
 *
 */

namespace Blast\Tests\Orm\Entity;


use Blast\Orm\Entity\Transformer;
use Blast\Orm\MapperInterface;
use Blast\Tests\Orm\Stubs\Entities\Post;
use Blast\Tests\Orm\Stubs\Entities\Role;
use League\Event\EmitterInterface;

class TransformerTest extends \PHPUnit_Framework_TestCase
{
    public function testTransformFromArray()
    {
        $configuration = [
            'entity' => Post::class,
            'tableName' => 'blog_posts'
        ];

        $transformer = new Transformer();
        $transformer->transform($configuration);

        $this->assertInstanceOf(Post::class, $transformer->getEntity());

        // table name definition is set to post within post class
        $this->assertEquals('post', $transformer->getDefinition()->getTableName());
        $this->assertInstanceOf(MapperInterface::class, $transformer->getDefinition()->getMapper());
        $this->assertInstanceOf(EmitterInterface::class, $transformer->getDefinition()->getEmitter());
    }

    public function testTransformFromEntity()
    {
        $transformer = new Transformer();
        $transformer->transform(Post::class);

        $this->assertInstanceOf(Post::class, $transformer->getEntity());
        $this->assertEquals('post', $transformer->getDefinition()->getTableName());
        $this->assertInstanceOf(MapperInterface::class, $transformer->getDefinition()->getMapper());
        $this->assertInstanceOf(EmitterInterface::class, $transformer->getDefinition()->getEmitter());
    }

    public function testTransformFromEntityWithTablePlural()
    {
        $transformer = new Transformer();
        $transformer->transform(Role::class);

        $this->assertEquals('roles', $transformer->getDefinition()->getTableName());
    }

    public function testTransformFromTableName()
    {
        $transformer = new Transformer();
        $transformer->transform('testTable');

        $this->assertInstanceOf(\ArrayObject::class, $transformer->getEntity());
        $this->assertEquals('testTable', $transformer->getDefinition()->getTableName());
        $this->assertInstanceOf(MapperInterface::class, $transformer->getDefinition()->getMapper());
        $this->assertInstanceOf(EmitterInterface::class, $transformer->getDefinition()->getEmitter());
    }
}
