<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 26.11.2015
 * Time: 15:40
 */

namespace Blast\Db\Orm;


use Blast\Db\Model\CollectionInterface;
use Blast\Db\Model\ModelInterface;
use Blast\Db\Manager;
use Blast\Db\Query;
use Doctrine\DBAL\Query\QueryBuilder;

interface MapperInterface
{
    /**
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection();

    /**
     * @return Manager
     */
    public function getFactory();

    /**
     * @return ModelInterface
     */
    public function getModel();

    /**
     * @return QueryBuilder
     */
    public function createQuery();

    /**
     *
     * @param $value
     * @return ModelInterface
     */
    public function find($value);

    /**
     * Get a collection of all entities
     *
     * @return array|CollectionInterface
     */
    public function all();

    /**
     * Get a statement and build a query. Table is already selected
     * @param array $selects
     * @return Query
     */
    public function select($selects = ['*']);

    /**
     * @param CollectionInterface|ModelInterface|array $model
     * @return int
     */
    public function create($model);

    /**
     * @param CollectionInterface|ModelInterface|array $model
     * @return int
     */
    public function update($model);

    /**
     * @param CollectionInterface|ModelInterface|array $model
     * @return int
     */
    public function delete($model);

    /**
     * @param CollectionInterface|ModelInterface|array $model
     * @return int
     */
    public function save($model);

}