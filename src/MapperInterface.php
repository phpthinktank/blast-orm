<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 26.11.2015
 * Time: 15:40
 */

namespace Blast\Orm;


use Blast\Orm\Entity\EntityInterface;
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
     * @param $data
     * @return int
     */
    public function create($data);

    /**
     * @param $data
     * @param $identifiers
     * @return int
     */
    public function update($data, $identifiers);

    /**
     * @param $identifiers
     * @return int
     */
    public function delete($identifiers);

}