<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 26.11.2015
 * Time: 15:40
 */

namespace Blast\Orm\Mapper;

use Blast\Orm\Mapper\Model\ModelInterface;
use Blast\Orm\Query\ResultCollection;

interface MapperInterface
{

    /**
     *
     * @param mixed $value
     * @param null|string $field
     * @return ModelInterface
     */
    public function find($value, $field = null);

    /**
     * Get a collection of all entities
     *
     * @return array|ResultCollection
     */
    public function all();

    /**
     * @param ModelInterface|array $entity
     * @return int
     */
    public function delete($entity);

    /**
     * @param ModelInterface|array $entity
     * @return int
     */
    public function save($entity);

}