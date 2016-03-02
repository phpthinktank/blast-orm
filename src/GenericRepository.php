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

namespace Blast\Orm;


class GenericRepository extends AbstractRepository
{

    /**
     * GenericRepository constructor.
     * @param object $entity
     */
    public function __construct($entity)
    {
        $this->setEntity($entity);
    }
}