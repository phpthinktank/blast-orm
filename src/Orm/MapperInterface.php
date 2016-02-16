<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 26.11.2015
 * Time: 15:40
 */

namespace Blast\Db\Orm;

use Blast\Db\Orm\Model\ModelInterface;
use Blast\Db\Query\ResultCollection;

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
     * @param ModelInterface|array $model
     * @return int
     */
    public function delete($model);

    /**
     * @param ModelInterface|array $model
     * @return int
     */
    public function save($model);

}