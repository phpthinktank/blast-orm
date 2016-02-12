<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 12.02.2016
 * Time: 10:29
 *
 */

namespace Blast\Db\Data;


interface ImmutableDataObjectInterface
{
    /**
     * Receive data
     * @return array
     */
    public function getData();

}