<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 16.03.2016
 * Time: 17:24
 *
 */

namespace Blast\Orm;


trait MapperFactoryTrait
{
    /**
     * Create a new Mapper for given entity or provider and
     * pass additional database connection.
     *
     * @param $entity
     * @param null $connection
     * @return Mapper
     */
    public function createMapper($entity, $connection = null){
        return new Mapper($entity, $connection);
    }
}
