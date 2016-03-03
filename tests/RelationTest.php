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
use Blast\Orm\ConnectionFacade;
use Blast\Orm\Data\DataObject;
use Blast\Orm\Entity\EntityAdapter;
use Blast\Orm\ConnectionCollection;
use Blast\Orm\Entity\EntityAdapterCollectionFacade;
use Blast\Orm\QueryInterface;
use Blast\Orm\Relations\BelongsTo;
use Blast\Orm\Relations\HasMany;
use Blast\Orm\Relations\HasOne;
use Blast\Orm\Relations\ManyToMany;
use Blast\Tests\Orm\Stubs\Entities\Address;
use Blast\Tests\Orm\Stubs\Entities\Post;
use Blast\Tests\Orm\Stubs\Entities\Role;
use Blast\Tests\Orm\Stubs\Entities\User;
use Interop\Container\ContainerInterface;

class RelationTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $connection = ConnectionFacade::addConnection( [
            'url' => 'sqlite:///:memory:',
            'memory' => 'true'
        ])->getConnection();

        $connection->exec('CREATE TABLE post (id int, user_pk int, title VARCHAR(255), content TEXT)');
        $connection->exec('CREATE TABLE user (pk int, name VARCHAR(255))');
        $connection->exec('CREATE TABLE address (id int, user_pk int, address TEXT)');
        $connection->exec('CREATE TABLE user_role (user_pk int, role_id int)');
        $connection->exec('CREATE TABLE role (id int, name VARCHAR(255))');
        $connection->insert('post', [
            'id' => 1,
            'user_pk' => 1,
            'title' => 'Hello World',
            'content' => 'Some text',
        ]);
        $connection->insert('post', [
            'id' => 2,
            'user_pk' => 1,
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
            'user_pk' => 1,
            'address' => 'street 42, 11111 city'
        ]);
        $connection->insert('role', [
            'id' => 1,
            'name' => 'Admin'
        ]);
    }

    protected function tearDown()
    {
        $connection = ConnectionFacade::getConnection(ConnectionCollectionInterface::DEFAULT_CONNECTION);

        $connection->exec('DROP TABLE post');
        $connection->exec('DROP TABLE user');
        $connection->exec('DROP TABLE address');
        $connection->exec('DROP TABLE user_role');
        $connection->exec('DROP TABLE role');

        ConnectionFacade::__destruct();
    }

    public function testBelongsTo()
    {
        $post = EntityAdapterCollectionFacade::get(Post::class)->getMapper()->find(1)->execute();
        $relation = new BelongsTo($post, User::class);

        $this->assertInstanceOf(QueryInterface::class, $relation->getQuery());
        $this->assertInstanceOf(User::class, $relation->execute());

    }

    public function testHasMany(){
        $user = EntityAdapterCollectionFacade::get(User::class)->getMapper()->find(1)->execute();
        $relation = new HasMany($user, Post::class);
        $this->assertInstanceOf(QueryInterface::class, $relation->getQuery());
        $posts = $relation->execute();
        $this->assertInstanceOf(DataObject::class, $posts);
        $this->assertInstanceOf(Post::class, $posts->current());

    }

    public function testHasOne(){
        $user = EntityAdapterCollectionFacade::get(User::class)->getMapper()->find(1)->execute();

        $relation = new HasOne($user, Address::class);
        $this->assertInstanceOf(QueryInterface::class, $relation->getQuery());

        $address = $relation->execute();
        $this->assertInstanceOf(Address::class, $address);

    }

    public function testManyToMany(){
        $user = EntityAdapterCollectionFacade::get(User::class)->getMapper()->find(1)->execute();
        $relation = new ManyToMany($user, Role::class);
        $this->assertInstanceOf(QueryInterface::class, $relation->getQuery());

        $result = $relation->execute();
        $this->assertInstanceOf(DataObject::class, $result);
        $this->assertInstanceOf(Role::class, $result->current());
    }
}
