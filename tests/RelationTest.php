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
        $post = $this->locator->getProvider(Post::class)->getMapper()->find(1)->execute();
        $relation = new BelongsTo($this->locator, $post, User::class);

        $query = $relation->getQuery();
        $sql = $query->getSQL();
        $this->assertInstanceOf(QueryInterface::class, $query);
        $this->assertInstanceOf(User::class, $relation->execute());

    }

    public function testHasMany()
    {
        $user = $this->locator->getProvider(User::class)->getMapper()->find(1)->execute();
        $relation = new HasMany($this->locator, $user, Post::class);
        $this->assertInstanceOf(QueryInterface::class, $relation->getQuery());
        $posts = $relation->execute();
        $this->assertInstanceOf(\SplStack::class, $posts);
        $this->assertInstanceOf(Post::class, $posts->current());

    }

    public function testHasOne()
    {
        $user = $this->locator->getProvider(User::class)->getMapper()->find(1)->execute();

        $relation = new HasOne($this->locator, $user, Address::class);
        $this->assertInstanceOf(QueryInterface::class, $relation->getQuery());

        $address = $relation->execute();
        $this->assertInstanceOf(Address::class, $address);

    }

    public function testManyToMany()
    {
        $user = $this->locator->getProvider(User::class)->getMapper()->find(1)->execute();
        $relation = new ManyToMany($this->locator, $user, Role::class);
        $this->assertInstanceOf(QueryInterface::class, $relation->getQuery());

        $result = $relation->execute();
        $this->assertInstanceOf(\SplStack::class, $result);
        $this->assertInstanceOf(Role::class, $result->current());
    }
}
