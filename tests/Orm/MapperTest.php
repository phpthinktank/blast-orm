<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 26.11.2015
 * Time: 16:09
 */

namespace Blast\Tests\Db\Orm;

use Blast\Db\ConfigInterface;
use Blast\Db\Entity\CollectionInterface;
use Blast\Db\Entity\EntityInterface;
use Blast\Db\Factory;
use Blast\Db\Orm\Mapper;
use Blast\Tests\Db\Stubs\Entities\Post;
use Interop\Container\ContainerInterface;
use Prophecy\Prophecy\ObjectProphecy;
use Stubs\Entities\User;

class MapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityInterface
     */
    private $entity;

    /**
     * @var ObjectProphecy
     */
    private $container;

    /**
     * @var Factory
     */
    private $factory;

    protected function setUp()
    {
        $this->markTestSkipped('Entity and models are in development. Mapper tests depend on entity and needs to be updated');

        $this->container = $this->prophesize(ContainerInterface::class)->willImplement(ContainerInterface::class);

        $container = $this->container->reveal();
        $factory = Factory::create($container, [
            'url' => 'sqlite:///:memory:',
            'memory' => 'true'
        ]);

        $this->entity = new Post();

        $connection = $factory->getConfig()->getConnection(ConfigInterface::DEFAULT_CONNECTION);
        $connection->prepare('CREATE TABLE post (id int, user_id int, title VARCHAR(255), content TEXT)')->execute();
        $connection->prepare('CREATE TABLE user (id int, name VARCHAR(255))')->execute();
        $connection->insert('post', [
            'id' => 1,
            'user_id' => 1,
            'title' => 'Hello World',
            'content' => 'Some text',
        ]);
        $connection->insert('post', [
            'id' => 2,
            'user_id' => 1,
            'title' => 'Next thing',
            'content' => 'More text to read'
        ]);
        $connection->insert('user', [
            'id' => 1,
            'name' => 'Franz'
        ]);
    }

    protected function tearDown()
    {
        $factory = Factory::getInstance();
        $connection = $factory->getConfig()->getConnection(ConfigInterface::DEFAULT_CONNECTION);
        $connection->prepare('DROP TABLE post')->execute();
        $connection->prepare('DROP TABLE user')->execute();
        $factory->shutdown();
    }

    /**
     * select any field
     */
    public function testSelect()
    {
        $entity = $this->entity;
        $mapper = new Mapper($entity);

        $query = $mapper->select();
        $result = $query->where('user_id = 1')->execute();

        $this->assertInstanceOf(CollectionInterface::class, $result);
        $this->assertEquals(2, $result->count());
    }

    /**
     * find by pk
     */
    public function testFind()
    {
        $entity = $this->entity;
        $mapper = new Mapper($entity);

        $result = $mapper->find(1);

        $this->assertInstanceOf(EntityInterface::class, $result);
    }

    /**
     * find by pk
     */
    public function testBelongsTo()
    {
        $entity = $this->entity;
        $mapper = new Mapper($entity);

        $result = $mapper->find(1);

        $this->assertInstanceOf(Post::class, $result);
        $this->assertInstanceOf(User::class, $result->user);
    }

    public function testCreateByEntity(){
        $entity = $this->entity;
        $mapper = new Mapper($entity);

        $post = new Post();
        $post->pk = 3;
        $post->user_id = 1;
        $post->ttile = 'first created post';
        $post->content = 'A new post!';

        $result = $mapper->create($post);

        $this->assertTrue(is_numeric($result) && !is_bool($result));
    }
}
