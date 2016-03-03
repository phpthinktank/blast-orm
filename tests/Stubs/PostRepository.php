<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 29.02.2016
 * Time: 11:17
 *
 */

namespace Blast\Tests\Orm\Stubs;


use Blast\Orm\AbstractRepository;
use Blast\Tests\Orm\Stubs\Entities\Post;

class PostRepository extends AbstractRepository
{

    public function __construct()
    {
        $this->setEntity(Post::class);
    }

}