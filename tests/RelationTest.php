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


use Blast\Orm\Entity\Provider;
use Blast\Orm\QueryInterface;
use Blast\Orm\Relations\BelongsTo;
use Blast\Orm\Relations\HasMany;
use Blast\Orm\Relations\HasOne;
use Blast\Orm\Relations\ManyToMany;
use Blast\Tests\Orm\Stubs\Entities\Address;
use Blast\Tests\Orm\Stubs\Entities\Post;
use Blast\Tests\Orm\Stubs\Entities\Role;
use Blast\Tests\Orm\Stubs\Entities\User;

class RelationTest extends AbstractDbTestCase
{

    public function testBelongsTo()
    {
        $provider = new Provider(Post::class);
        $post = $provider->getDefinition()->getMapper()->find(1)->execute();
        $relation = new BelongsTo($post, User::class);

        $query = $relation->getQuery();
        $sql = $query->getSQL();
        $this->assertInstanceOf(QueryInterface::class, $query);
        $this->assertInstanceOf(User::class, $relation->execute());

    }

    public function testHasMany()
    {
        $provider = new Provider(User::class);
        $user = $provider->getDefinition()->getMapper()->find(1)->execute();
        $relation = new HasMany($user, Post::class);
        $this->assertInstanceOf(QueryInterface::class, $relation->getQuery());
        $posts = $relation->execute();
        $this->assertInstanceOf(\SplStack::class, $posts);
        $this->assertInstanceOf(Post::class, $posts->current());

    }

    public function testHasOne()
    {
        $provider = new Provider(User::class);
        $user = $provider->getDefinition()->getMapper()->find(1)->execute();

        $relation = new HasOne($user, Address::class);
        $this->assertInstanceOf(QueryInterface::class, $relation->getQuery());

        $address = $relation->execute();
        $this->assertInstanceOf(Address::class, $address);

    }

    public function testManyToMany()
    {
        $provider = new Provider(User::class);
        $user = $provider->getDefinition()->getMapper()->find(1)->execute();
        $relation = new ManyToMany($user, Role::class);
        $this->assertInstanceOf(QueryInterface::class, $relation->getQuery());

        $result = $relation->execute();
        $this->assertInstanceOf(\SplStack::class, $result);
        $this->assertInstanceOf(Role::class, $result->current());
    }
}
