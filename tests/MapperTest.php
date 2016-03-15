<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 26.11.2015
 * Time: 16:09
 */

namespace Blast\Tests\Orm;

use Blast\Orm\Mapper;
use Blast\Tests\Orm\Stubs\Entities\Post;
use Blast\Tests\Orm\Stubs\Entities\User;

class MapperTest extends AbstractDbTestCase
{

    /**
     * select any field
     */
    public function testSelect()
    {
        $mapper = new Mapper($this->locator, new Post());

        $query = $mapper->select();
        $result = $query->where('user_pk = 1')->execute();

        $this->assertInstanceOf(\SplStack::class, $result);
        $this->assertEquals(2, $result->count());
    }

    /**
     * find by pk
     */
    public function testFind()
    {
        $mapper = new Mapper($this->locator, new Post);

        $result = $mapper->find(1)->execute();

        $this->assertInstanceOf(Post::class, $result);
    }

    /**
     * create new entry
     */
    public function testCreate()
    {
        $mapper = new Mapper($this->locator, new Post);

        $post = new Post();
        $post['id'] = 3;
        $post['user_pk'] = 1;
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
        $mapper = new Mapper($this->locator, new Post);

        $post = new Post();

        $query = $mapper->create($post);

        $this->assertFalse($query);
    }

    /**
     * update existing entry
     */
    public function testUpdate()
    {
        $mapper = new Mapper($this->locator, new Post);
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
        $mapper = new Mapper($this->locator, new Post);

        $post = new Post();
        $post['user_pk'] = 1;
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
        $mapper = new Mapper($this->locator, new Post);
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
        $mapper = new Mapper($this->locator, new Post);
        $result = $mapper->delete(1)->execute();

        $this->assertEquals($result, 1);
    }

    public function testPlainObjectImplementation()
    {
        $mapper = new Mapper($this->locator, User::class);
        $user = $mapper->find(1)->execute();

        $this->assertInstanceOf(User::class, $user);
    }
}
