<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 26.11.2015
 * Time: 15:40
 */

namespace Blast\Db\Orm;


use Blast\Db\Entity\CollectionInterface;
use Blast\Db\Entity\EntityInterface;
use Blast\Db\Factory;
use Blast\Db\Query;
use Doctrine\DBAL\Query\QueryBuilder;

interface MapperInterface
{
    /**
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection();

    /**
     * @return Factory
     */
    public function getFactory();

    /**
     * @return EntityInterface
     */
    public function getEntity();

    /**
     * @return QueryBuilder
     */
    public function createQuery();

    /**
     *
     * @param $value
     * @return EntityInterface
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
     * @param CollectionInterface|EntityInterface|array $entity
     * @return int
     */
    public function create($entity);

    /**
     * @param CollectionInterface|EntityInterface|array $entity
     * @return int
     */
    public function update($entity);

    /**
     * @param CollectionInterface|EntityInterface|array $entity
     * @return int
     */
    public function delete($entity);

    /**
     * @param CollectionInterface|EntityInterface|array $entity
     * @return int
     */
    public function save($entity);

}