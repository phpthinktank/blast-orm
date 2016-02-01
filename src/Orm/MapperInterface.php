<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 26.11.2015
 * Time: 15:40
 */

namespace Blast\Db\Orm;


use Blast\Db\Entity\EntityInterface;
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
    public function getQueryBuilder();

    /**
     * @param $pk
     * @return array
     */
    public function find($pk);

    /**
     * @param $field
     * @param $value
     * @return array
     */
    public function findBy($field, $value);

    /**
     * @param $statement
     * @return array
     */
    public function fetch(QueryBuilder $statement);

    /**
     * @param EntityInterface $entity
     * @return int
     */
    public function create($entity);

    /**
     * @param EntityInterface|EntityInterface[] $entity
     * @return int
     */
    public function update($entity);

    /**
     * @param EntityInterface|EntityInterface[] $entity
     * @return int
     */
    public function delete($entity);

    /**
     * @param EntityInterface|EntityInterface[] $entity
     * @return int
     */
    public function save($entity);

}