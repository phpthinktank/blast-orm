<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 29.02.2016
 * Time: 13:26
 *
 */

namespace Blast\Tests\Orm;


use Blast\Orm\ConnectionCollectionInterface;
use Blast\Orm\Data\DataObject;
use Blast\Orm\Entity\EntityAdapter;
use Blast\Orm\Manager;
use Blast\Orm\Mapper;
use Blast\Orm\Relations\RelationInterface;
use Blast\Tests\Orm\Stubs\Entities\Post;
use Blast\Tests\Orm\Stubs\Entities\User;
use Blast\Tests\Orm\Stubs\PostRepository;
use Interop\Container\ContainerInterface;

class RelationTest extends \PHPUnit_Framework_TestCase
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
        $connection->exec('CREATE TABLE address (id int, user_id int, data TEXT)');
        $connection->exec('CREATE TABLE user_role (user_pk int, role_id int)');
        $connection->exec('CREATE TABLE role (id int, name VARCHAR(255))');
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
        $connection->insert('user_role', [
            'user_pk' => 1,
            'role_id' => 1
        ]);
        $connection->insert('address', [
            'id' => 1,
            'user_id' => 1,
            'data' => 'street 42, 11111 city'
        ]);
        $connection->insert('role', [
            'id' => 1,
            'name' => 'Admin'
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

    public function testBelongsTo()
    {
        $postRepository = new PostRepository();
        $post = $postRepository->find(1);

        $postData = $post->getData();
        $this->assertArrayHasKey('user', $postData);
        $this->assertInstanceOf(RelationInterface::class, $postData['user']);
        $this->assertInstanceOf(User::class, $postData['user']->getQuery()->execute());

    }

    public function testHasMany(){
        $adapter = new EntityAdapter(new User);
        $mapper = $adapter->getMapper();
        $user = $mapper->find(1)->execute();

        $this->assertInstanceOf(User::class, $user);

        $posts = $user->getPost()->getQuery()->execute();
        $this->assertInstanceOf(DataObject::class, $posts);
        $this->assertInstanceOf(Post::class, $posts->current());

    }

    public function testHasOne(){
        $adapter = new EntityAdapter(new User);
        $mapper = $adapter->getMapper();
        $user = $mapper->find(1)->execute();

        $this->assertInstanceOf(User::class, $user);

        var_dump($user->getAddress()->getQuery()->execute());



//        $posts = $user->getPost()->getQuery()->execute();
//        $this->assertInstanceOf(DataObject::class, $posts);
//        $this->assertInstanceOf(Post::class, $posts->current());

    }

    public function testManyToMany(){
        $adapter = new EntityAdapter(new User);
        $mapper = $adapter->getMapper();
        $user = $mapper->find(1)->execute();

        $this->assertInstanceOf(User::class, $user);

        $posts = $user->getPost()->getQuery()->execute();
        $this->assertInstanceOf(DataObject::class, $posts);
        $this->assertInstanceOf(Post::class, $posts->current());

    }
}
