<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 12.02.2016
 * Time: 10:41
 *
 */

namespace Blast\Db\Data;


trait CountableTrait
{
    /**
     * get count of data
     *
     * @return int
     */
    public function count()
    {
        return count(DataHelper::receiveDataFromObject($this));
    }
}