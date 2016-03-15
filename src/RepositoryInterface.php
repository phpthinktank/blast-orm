<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 29.02.2016
 * Time: 10:20
 *
 */

namespace Blast\Orm;


interface RepositoryInterface
{
    /**
     * Find entity by primary key
     *
     * @param mixed $primaryKey
     * @return \ArrayObject|\stdClass|object|array
     */
    public function find($primaryKey);

    /**
     * Get a collection of all entities
     *
     * @return \SplStack|array
     */
    public function all();

    /**
     * Save new or existing entity
     *
     * @param object|array $data
     * @return int
     */
    public function save($data);
}
