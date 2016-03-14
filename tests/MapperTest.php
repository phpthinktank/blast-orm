<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 26.11.2015
 * Time: 16:09
 */

namespace Blast\Tests\Orm;

use Blast\Orm\ConnectionManagerInterface;
use Blast\Orm\ConnectionFacade;
use Blast\Orm\Data\DataObject;
use Blast\Orm\ConnectionManager;
use Blast\Orm\LocatorFacade;
use Blast\Orm\Mapper;
use Blast\Tests\Orm\Stubs\Entities\Post;
use Blast\Tests\Orm\Stubs\Entities\User;
use Interop\Container\ContainerInterface;

class MapperTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        $connection = LocatorFacade::getConnectionManager()->add( [
            'url' => 'sqlite:///:memory:',
            'memory' => 'true'
        ])->get();

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
        $connection = LocatorFacade::getConnectionManager()->get(ConnectionManagerInterface::DEFAULT_CONNECTION);
        $connection->exec('DROP TABLE post');
        $connection->exec('DROP TABLE user');

        LocatorFacade::getConnectionManager()->closeAll();
    }

    /**
     * select any field
     */
    public function testSelect()
    {
        $mapper = new Mapper(new Post());

        $query = $mapper->select();
        $result = $query->where('user_id = 1')->execute();

        $this->assertInstanceOf(\SplStack::class, $result);
        $this->assertEquals(2, $result->count());
    }

    /**
     * find by pk
     */
    public function testFind()
    {
        $mapper = new Mapper(new Post);

        $result = $mapper->find(1)->execute();

        $this->assertInstanceOf(Post::class, $result);
    }

    /**
     * create new entry
     */
    public function testCreate()
    {
        $mapper = new Mapper(new Post);

        $post = new Post();
        $post['id'] = 3;
        $post['user_id'] = 1;
        $post['title'] = 'first created post';
        $post['content'] = 'A new post!';

        $result = $mapper->create($post)->execute();

        $this->assertEquals($result, 1);
    }

    /**
     * create new entry
     */
    public function testCreateNothing()
    {
        $mapper = new Mapper(new Post);

        $post = new Post();

        $query = $mapper->create($post);

        $this->assertFalse($query);
    }

    /**
     * update existing entry
     */
    public function testUpdate()
    {
        $mapper = new Mapper(new Post);
        $result = $mapper->find(1)->execute();
        $this->assertInstanceOf(Post::class, $result);
        $result['title'] = $result['title'] . ' Again!';

        $this->assertEquals(1, $mapper->update($result)->execute());
    }

    /**
     * update existing entry
     */
    public function testSaveANewEntry()
    {
        $mapper = new Mapper(new Post);

        $post = new Post();
        $post['user_id'] = 1;
        $post['title'] = 'first created post';
        $post['content'] = 'A new post!';

        $result = $mapper->save($post)->execute();

        $this->assertEquals($result, 1);
    }

    /**
     * update existing entry
     */
    public function testSaveExisting()
    {
        $mapper = new Mapper(new Post);
        $result = $mapper->find(1)->execute();
        $this->assertInstanceOf(Post::class, $result);
        $result['title'] = $result['title'] . ' Again!';

        $this->assertEquals(1, $mapper->save($result)->execute());
    }

    /**
     * delete entry by pk
     */
    public function testDelete()
    {
        $mapper = new Mapper(new Post);
        $result = $mapper->delete(1)->execute();

        $this->assertEquals($result, 1);
    }

    public function testPlainObjectImplementation()
    {
        $mapper = new Mapper(User::class);
        $user = $mapper->find(1)->execute();

        $this->assertInstanceOf(User::class, $user);
    }
}
