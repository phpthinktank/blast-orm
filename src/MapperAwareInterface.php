<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 01.03.2016
 * Time: 10:42
 *
 */

namespace Blast\Orm;


interface MapperAwareInterface
{
    /**
     * Get entity mapper
     *
     * @return MapperInterface
     */
    public function getMapper();
}