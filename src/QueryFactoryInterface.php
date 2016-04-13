<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 13.04.2016
 * Time: 08:34
 *
 */

namespace Blast\Orm;


interface QueryFactoryInterface
{
    /**
     * Create a new query for given entity.
     *
     * @param $entity
     * 
     * @return Mapper
     */
    public function createQuery($entity = null);
}
