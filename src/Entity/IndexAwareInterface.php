<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 01.03.2016
 * Time: 09:35
 *
 */

namespace Blast\Orm\Entity;


use Doctrine\DBAL\Schema\Index;

interface IndexAwareInterface
{
    /**
     * @return Index[]
     */
    public function getIndexes();
}