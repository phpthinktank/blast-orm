<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 26.11.2015
 * Time: 15:40
 */

namespace Blast\Orm;

use Blast\Orm\Data\DataObject;
use Blast\Orm\Query\Result;

interface RepositoryInterface
{

    /**
     *
     * @param mixed $value
     * @return \ArrayObject|\stdClass|Result|DataObject|object
     */
    public function find($value);

    /**
     * Get a collection of all entities
     *
     * @return \ArrayObject|\stdClass|DataObject|object
     */
    public function all();

    /**
     * Delete attached entity by identifiers
     *
     * @param array|int|string $identifiers
     * @return int
     */
    public function delete($identifiers);

    /**
     * Create or update an entity
     *
     * Optional force update of entities without updates
     *
     * @param DataObject|\ArrayObject|\stdClass|Result|object $entity
     * @return int
     */
    public function save($entity);

}