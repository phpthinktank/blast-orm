<?php
/*
*
* (c) Marco Bunge <marco_bunge@web.de>
*
* For the full copyright and license information, please view the LICENSE.txt
* file that was distributed with this source code.
*
* Date: 08.02.2016
* Time: 16:11
*/

namespace Blast\Orm;

use Blast\Orm\Data\DataObject;
use Blast\Orm\Events\BuilderEvent;
use Blast\Orm\Events\ResultEvent;
use Blast\Orm\Query\Result;
use Blast\Orm\Query\ResultDataDecorator;
use Doctrine\DBAL\Query\QueryBuilder;
use League\Event\EmitterAwareInterface;
use League\Event\EmitterAwareTrait;
use stdClass;

/**
 * Class Query
 *
 * @method \Doctrine\DBAL\Query\Expression\ExpressionBuilder expr()
 * @method int getType()
 * @method int getState()
 * @method string getSQL()
 * @method Query setParameter($key, $value, $type = null)
 * @method Query setParameters(array $params, array $types = array())
 * @method array getParameters()
 * @method mixed getParameter($key)
 * @method array getParameterTypes()
 * @method mixed getParameterType($key)
 * @method Query setFirstResult($firstResult)
 * @method int getFirstResult()
 * @method Query setMaxResults($maxResults)
 * @method int getMaxResults()
 * @method Query add($sqlPartName, $sqlPart, $append = false)
 * @method Query addSelect($select = null)
 * @method Query delete($delete = null, $alias = null)
 * @method Query update($update = null, $alias = null)
 * @method Query insert($insert = null)
 * @method Query from($from, $alias = null)
 * @method Query join($fromAlias, $join, $alias, $condition = null)
 * @method Query innerJoin($fromAlias, $join, $alias, $condition = null)
 * @method Query leftJoin($fromAlias, $join, $alias, $condition = null)
 * @method Query rightJoin($fromAlias, $join, $alias, $condition = null)
 * @method Query set($key, $value)
 * @method Query where($predicates)
 * @method Query andWhere($where)
 * @method Query orWhere($where)
 * @method Query groupBy($groupBy)
 * @method Query addGroupBy($groupBy)
 * @method Query setValue($column, $value)
 * @method Query values(array $values)
 * @method Query having($having)
 * @method Query andHaving($having)
 * @method Query orHaving($having)
 * @method Query orderBy($sort, $order = null)
 * @method Query addOrderBy($sort, $order = null)
 * @method mixed getQueryPart($queryPartName)
 * @method array getQueryParts()
 * @method Query resetQueryParts($queryPartNames = null)
 * @method Query resetQueryPart($queryPartName)
 * @method string __toString()
 * @method string createNamedParameter($value, $type = \PDO::PARAM_STR, $placeHolder = null)
 * @method string createPositionalParameter($value, $type = \PDO::PARAM_STR)
 * @method void __clone()
 *
 * @package Blast\Db\Orm
 */
class Query implements EmitterAwareInterface
{

    use EmitterAwareTrait;
    use EntityAwareTrait;

    /**
     * @var QueryBuilder
     */
    private $builder;

    /**
     * Statement constructor.
     * @param array|stdClass|\ArrayObject|object|string $entity
     * @param Query $builder
     */
    public function __construct($entity = null, $builder = null)
    {
        $this->builder = $builder === null ? Manager::getInstance()->getConnection()->createQueryBuilder() : $builder;
        $this->entity = $entity;
    }

    /**
     * @return QueryBuilder
     */
    public function getBuilder()
    {
        return $this->builder;
    }

    /**
     * Fetch data for entity
     *
     * @param string $option
     * @return array|Result|DataObject
     * @throws \Doctrine\DBAL\DBALException
     */
    public function execute($option = ResultDataDecorator::AUTO)
    {
        //execute before events and proceed with builder from event
        $builder = $this->beforeExecute($this->getEntity());
        $entity = $builder->getEntity();

        if (!$builder) {
            return false;
        }

        $connection = Manager::getInstance()->getConnection();

        $isSelect = $builder->getType() === QueryBuilder::SELECT;

        $statement = $isSelect ?
            //execute query and receive a statement
            $connection->executeQuery($this->getSQL(), $this->getParameters(), $this->getParameterTypes()) :

            //execute query and receive a count of affected rows
            $connection->executeUpdate($this->getSQL(), $this->getParameters(), $this->getParameterTypes());

        //execute after events and proceed with result from event
        $result = $this->afterExecute(
            $isSelect ?
                $statement->fetchAll() :
                $statement,
            $entity, $builder);

        if (!$result) {
            return false;
        }

        $decorator = new ResultDataDecorator($result, $entity);

        return $decorator->decorate($option);
    }

    /**
     * Get query type name
     * @return string
     * @throws \Exception
     */
    public function getTypeName()
    {
        switch ($this->getType()) {
            case QueryBuilder::SELECT:
                return 'select';
            case QueryBuilder::INSERT:
                return 'insert';
            case QueryBuilder::UPDATE:
                return 'update';
            case QueryBuilder::DELETE:
                return 'delete';
            default:
                throw new \Exception('Unknown query type ' . $this->getType());
        }
    }

    /**
     * Magic call of \Doctrine\DBAL\Query\QueryBuilder methods
     *
     * @param string|callable $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, array $arguments = [])
    {
        $result = call_user_func_array([$this->getBuilder(), $name], $arguments);
        return $result instanceof QueryBuilder ? $this : $result;
    }

    /**
     * Emit events before query handling and if entity is able to emit events execute entity events
     *
     * @param $entity
     * @return Query
     */
    private function beforeExecute($entity)
    {
        $builder = $this;
        $event = $this->getEmitter()->emit(new BuilderEvent('before.' . $this->getTypeName(), $builder));

        if ($entity instanceof EmitterAwareInterface) {
            $event = $entity->getEmitter()->emit($event, $builder);
        }

        if ($event instanceof BuilderEvent) {
            $builder = $event->getBuilder();
        }


        return $builder;
    }

    /**
     * Emit events after query handling and if entity is able to emit events execute entity events
     *
     * @param mixed $result Raw result
     * @param mixed $entity Entity which contains the events
     * @param Query $builder
     * @return array
     */
    private function afterExecute($result, $entity, $builder)
    {

        $event = $this->getEmitter()->emit(new ResultEvent('after.' . $builder->getTypeName(), $result), $builder);

        if ($entity instanceof EmitterAwareInterface) {
            $event = $entity->getEmitter()->emit($event, $builder);
        }

        if ($event instanceof ResultEvent) {
            $result = $event->getResult();
        }

        return $result;
    }

    /*
     * --------------------------------------------------------------------------
     *              IMPROVED QUERY BUILDER METHODS
     * --------------------------------------------------------------------------
     */

    /**
     * Specifies an item that is to be returned in the query result.
     * Replaces any previously specified selections, if any.
     *
     * <code>
     *     $qb = $conn->createQueryBuilder()
     *         ->select('u.id', 'p.id')
     *         ->from('users', 'u')
     *         ->leftJoin('u', 'phonenumbers', 'p', 'u.id = p.user_id');
     * </code>
     *
     * @param array $select The selection expressions.
     *
     * @return $this Query instance
     */
    public function select(array $select = ['*'])
    {
        if (empty($select)) {
            $select = ['*'];
        }

        return $this->__call(__FUNCTION__, [$select]);
    }

}