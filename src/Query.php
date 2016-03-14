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

use Blast\Orm\Entity\EntityAwareTrait;
use Blast\Orm\Hydrator\ArrayToObjectHydrator;
use Blast\Orm\Hydrator\HydratorInterface;
use Blast\Orm\Query\Events\QueryBuilderEvent;
use Blast\Orm\Query\Events\QueryResultEvent;
use Doctrine\DBAL\Driver\Connection;
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
class Query implements EmitterAwareInterface, QueryInterface
{

    use EmitterAwareTrait;
    use EntityAwareTrait;

    /**
     * @var QueryBuilder
     */
    private $builder;

    /**
     * @var \Doctrine\DBAL\Driver\Connection
     */
    private $connection;

    /**
     * Statement constructor.
     * @param array|stdClass|\ArrayObject|object|string $entity
     * @param Query $builder
     * @param null|\Doctrine\DBAL\Driver\Connection $connection
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct($entity = null, $builder = null, $connection = null)
    {
        $this->connection = null === $connection ? LocatorFacade::getConnectionManager()->get() : $connection;
        $this->builder = $builder === null ? $this->connection->createQueryBuilder() : $builder;
        $this->setEntity($entity);
    }

    /**
     * @return QueryBuilder
     */
    public function getBuilder()
    {
        return $this->builder;
    }

    /**
     * @param QueryBuilder $builder
     */
    public function setBuilder($builder)
    {
        $this->builder = $builder;
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        if(null === $this->connection){
            $this->connection = LocatorFacade::getConnectionManager()->get();
        }
        return $this->connection;
    }

    /**
     * @param Connection $connection
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * Fetch data for entity
     *
     * @param string $option
     * @return array|\SplStack|\ArrayObject|bool
     * @throws \Doctrine\DBAL\DBALException
     */
    public function execute($option = HydratorInterface::HYDRATE_AUTO)
    {
        //execute before events and proceed with builder from event
        $provider = LocatorFacade::getProvider($this->getEntity());
        $event = $this->beforeExecute($provider);

        if ($event->isCanceled()) {
            return false;
        }

        $builder = $event->getBuilder();

        //convert entity to adapter again
        $provider = LocatorFacade::getProvider($builder->getEntity());

        //@todo this should be more dynamic for passing other connections
        $connection = LocatorFacade::getConnectionManager()->get();
        $isSelect = $builder->getType() === QueryBuilder::SELECT;

        $sql = $this->getSQL();
        if(true){

        }
        $statement = $isSelect ?
            //execute query and receive a statement
            $connection->executeQuery($sql, $this->getParameters(), $this->getParameterTypes()) :

            //execute query and receive a count of affected rows
            $connection->executeUpdate($sql, $this->getParameters(), $this->getParameterTypes());

        //execute after events and proceed with result from event
        $event = $this->afterExecute(
            $isSelect ?
                $statement->fetchAll() :
                $statement,
            $provider, $builder);

        if ($event->isCanceled()) {
            return false;
        }

        $result = $event->getResult();
        $data = (new ArrayToObjectHydrator($provider->getEntity()))->hydrate($result, $option);
        gc_collect_cycles();

        return $data;
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
            // @codeCoverageIgnoreStart
            default:
                //this could only happen if query will be extended and a custom getType is return invalid type
                throw new \Exception('Unknown query type ' . $this->getType());
        }
        // @codeCoverageIgnoreEnd
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
     * @return QueryBuilderEvent
     */
    private function beforeExecute($entity)
    {
        $builder = $this;
        $event = $this->getEmitter()->emit(new QueryBuilderEvent('before.' . $this->getTypeName(), $builder));

        if ($entity instanceof EmitterAwareInterface) {
            $event = $entity->getEmitter()->emit($event, $builder);
        }

        return $event;
    }

    /**
     * Emit events after query handling and if entity is able to emit events execute entity events
     *
     * @param mixed $result Raw result
     * @param mixed $entity Entity which contains the events
     * @param Query $builder
     * @return QueryResultEvent
     */
    private function afterExecute($result, $entity, $builder)
    {

        $event = $this->getEmitter()->emit(new QueryResultEvent('after.' . $builder->getTypeName(), $result), $builder);

        if ($entity instanceof EmitterAwareInterface) {
            $event = $entity->getEmitter()->emit($event, $builder);
        }

        return $event;
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
    public function select(array $select = [])
    {
        if (count($select) < 1) {
            $select = ['*'];
        }

        return $this->__call(__FUNCTION__, [$select]);
    }

}