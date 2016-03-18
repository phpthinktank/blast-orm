<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 18.03.2016
 * Time: 07:40
 *
 */

namespace Blast\Tests\Orm;


use Blast\Orm\TransactionHistory;
use Blast\Tests\Orm\Stubs\Entities\Post;
use Doctrine\DBAL\Query\QueryBuilder;

class TransactionHistoryTest extends AbstractDbTestCase
{

    public function testQueryNotifiesMapper()
    {
        $history = new TransactionHistory();

        $post1 = new Post();
        $post1['title'] = 'hello';

        $post2 = new Post();
        $post2['id'] = 2;
        $post2['title'] = 'hello';

        $post1id = $history->uniqueId();
        $history->store($post1id, $post1, QueryBuilder::INSERT);
        $post2id = $history->uniqueId();
        $history->store($post2id, $post2, QueryBuilder::SELECT);
        $history->store($post2id, $post2, QueryBuilder::DELETE);
        $history->store($post2id, $post2, QueryBuilder::DELETE);

        $this->assertTrue($history->getStorage()->offsetExists($post1id));
        $this->assertTrue($history->getStorage()->offsetExists($post2id));
        $this->assertEquals(2, $history->getStorage()->count());
        $this->assertEquals(1, count($history->filter(false, QueryBuilder::DELETE)));

    }
}
