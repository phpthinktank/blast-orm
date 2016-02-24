<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 26.11.2015
 * Time: 16:09
 */

namespace Blast\Tests\Orm;

use Blast\Orm\ConnectionCollectionInterface;
use Blast\Orm\Data\DataObject;
use Blast\Orm\Manager;
use Blast\Orm\Repository;
use Blast\Tests\Orm\Stubs\Entities\Post;
use Blast\Tests\Orm\Stubs\Entities\User;
use Interop\Container\ContainerInterface;

class RepositoryTest extends \PHPUnit_Framework_TestCase
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
        $connection->exec('CREATE TABLE user (pk int, name VARCHAR(255))');
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
            'pk' => 1,
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
        $mapper = new Repository(new Post());

        var_dump($mapper->loadAdapter($mapper->getEntity())->getPrimaryKeyName());

        $query = $mapper->select();
        $result = $query->where('user_id = 1')->execute();

        $this->assertInstanceOf(DataObject::class, $result);
        $this->assertEquals(2, $result->count());
    }

    /**
     * find by pk
     */
    public function testFind()
    {
        $mapper = new Repository(new Post);

        $result = $mapper->find(1);

        $this->assertInstanceOf(Post::class, $result);
    }

    /**
     * find all
     */
    public function testAll()
    {
        $mapper = new Repository(new Post);

        $result = $mapper->all();

        $this->assertInstanceOf(DataObject::class, $result);
        $this->assertNotInstanceOf(Post::class, $result);
    }

    /**
     * create new entry
     */
    public function testCreate(){
        $mapper = new Repository(new Post);

        $post = new Post();
        $post->id = 3;
        $post->user_id = 1;
        $post->title = 'first created post';
        $post->content = 'A new post!';

        $result = $mapper->create($post);

        $this->assertEquals($result, 1);
    }

    /**
     * update existing entry
     */
    public function testUpdate()
    {
        $mapper = new Repository(new Post);
        $result = $mapper->find(1);
        $this->assertInstanceOf(Post::class, $result);
        $result->title .= ' Again!';

        $this->assertEquals(1, $mapper->update($result));
    }

    /**
     * delete entry by pk
     */
    public function testDelete(){
        $mapper = new Repository(new Post);
        $result = $mapper->delete(1);

        $this->assertEquals($result, 1);
    }

    public function testPlainObjectImplementation(){
        $mapper = new Repository(User::class);
        $user = $mapper->find(1);

        $this->assertInstanceOf(User::class, $user);
    }
}
