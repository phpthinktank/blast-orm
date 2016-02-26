<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 12.02.2016
 * Time: 10:38
 *
 */

namespace Blast\Orm\Data;


interface FilterableInterface
{
    /**
     * Filter data
     *
     * @param callable $filter
     * @return array
     */
    public function filter(callable $filter);
}