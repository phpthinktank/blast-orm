<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 26.11.2015
 * Time: 16:09
 */

namespace Blast\Tests\Orm\Mapper;

use Blast\Orm\ConnectionCollectionInterface;
use Blast\Orm\Manager;
use Blast\Orm\Mapper\Mapper;
use Blast\Tests\Orm\Stubs\Entities\Post;
use Interop\Container\ContainerInterface;
use Stubs\Entities\User;

class MapperTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        $container = $this->prophesize(ContainerInterface::class)->willImplement(ContainerInterface::class)->reveal();
        $manager = Manager::create($container, [
            'url' => 'sqlite:///:memory:',
            'memory' => 'true'
        ]);

        $connection = $manager->getConnection();
        $connection->exec('CREATE TABLE post (id int, user_id int, title VARCHAR(255), content TEXT)');
        $connection->exec('CREATE TABLE user (id int, name VARCHAR(255))');
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
        $manager = Manager::getInstance();
        $connection = $manager->getConnection(ConnectionCollectionInterface::DEFAULT_CONNECTION);
        $connection->exec('DROP TABLE post');
        $connection->exec('DROP TABLE user');
        Manager::shutdown();
    }

    /**
     * select any field
     */
    public function testSelect()
    {
        $mapper = new Mapper(new Post());

        $query = $mapper->select();
        $result = $query->where('user_id = 1')->execute();

        $this->assertInstanceOf(ResultCollection::class, $result);
        $this->assertEquals(2, $result->count());
    }

    /**
     * find by pk
     */
    public function testFind()
    {
        $model = $this->model;
        $mapper = new Mapper($model);

        $result = $mapper->find(1);

        $this->assertInstanceOf(ModelInterface::class, $result);
    }

    /**
     * find by pk
     */
    public function testBelongsTo()
    {
        $model = $this->model;
        $mapper = new Mapper($model);

        $result = $mapper->find(1);

        $this->assertInstanceOf(Post::class, $result);
        $this->assertInstanceOf(User::class, $result->user);
    }

    public function testCreateByEntity(){
        $model = $this->model;
        $mapper = new Mapper($model);

        $post = new Post();
        $post->pk = 3;
        $post->user_id = 1;
        $post->ttile = 'first created post';
        $post->content = 'A new post!';

        $result = $mapper->create($post);

        $this->assertTrue(is_numeric($result) && !is_bool($result));
    }
}
