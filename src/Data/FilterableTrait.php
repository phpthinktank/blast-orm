<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 12.02.2016
 * Time: 10:39
 *
 */

namespace Blast\Db\Data;


trait FilterableTrait
{
    /**
     * Filter containing entities
     *
     * @param callable $filter
     * @return array
     */
    public function filter(callable $filter)
    {
        return array_filter(Helper::receiveDataFromObject($this), $filter, ARRAY_FILTER_USE_BOTH);
    }
}