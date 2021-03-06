<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 16.03.2016
 * Time: 17:25
 *
 */

namespace Blast\Orm;


interface MapperFactoryInterface
{
    /**
     * Create a new Mapper for given entity.
     *
     * @param $entity
     * @return Mapper
     */
    public function createMapper($entity);

}
