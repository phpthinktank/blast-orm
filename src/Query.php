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

use Blast\Orm\Entity\EntityAwareInterface;
use Blast\Orm\Entity\EntityAwareTrait;
use Blast\Orm\Entity\ProviderFactoryInterface;
use Blast\Orm\Entity\ProviderFactoryTrait;
use Blast\Orm\Entity\ProviderInterface;
use Blast\Orm\Hydrator\EntityHydrator;
use Blast\Orm\Hydrator\HydratorInterface;
use Blast\Orm\Query\Events\QueryBuilderEvent;
use Blast\Orm\Query\Events\QueryResultEvent;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Type;
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
 * @method Query setParameters(array $params, array $types = [])
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
class Query implements ConnectionAwareInterface, EmitterAwareInterface,
    EntityAwareInterface, ProviderFactoryInterface, QueryInterface
{
    use ConnectionAwareTrait;
    use EmitterAwareTrait;
    use EntityAwareTrait;
    use ProviderFactoryTrait;

    /**
     * @var QueryBuilder
     */
    private $builder;

    /**
     * Statement constructor.
     *
     * @param \Doctrine\DBAL\Connection $connection
     * @param array|stdClass|\ArrayObject|object|string $entity
     */
    public function __construct($connection = null, $entity = null)
    {
        $this->setConnection($connection);
        $this->setEntity($entity);
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
        $provider = $this->createProvider($this->getEntity());
        $event = $this->beforeExecute($provider->getEntity());

        if ($event->isCanceled()) {
            return false;
        }

        $builder = $event->getBuilder();

        //convert entity to adapter again
        $provider = $this->createProvider($builder->getEntity());

        $connection = $this->getConnection();
        $isSelect = $builder->getType() === QueryBuilder::SELECT;

        $sql = $this->getSQL();

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
            $provider->getEntity(), $builder);

        if ($event->isCanceled()) {
            return false;
        }

        $result = $this->convertTypesToPHPValues($provider, $event->getResult());

        $data = (new EntityHydrator($provider))->hydrate($result, $option);
        gc_collect_cycles();

        return $data;
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
        $event = $this->getEmitter()->emit(new QueryBuilderEvent('build.' . $this->getTypeName(), $builder));

        if ($entity instanceof EmitterAwareInterface) {
            $event = $entity->getEmitter()->emit($event);
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

        $event = $this->getEmitter()->emit(new QueryResultEvent('result.' . $builder->getTypeName(), $result), $builder);

        if ($entity instanceof EmitterAwareInterface) {
            $event = $entity->getEmitter()->emit($event, $builder);
        }

        return $event;
    }

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
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function getBuilder()
    {
        if (null === $this->builder) {
            $this->builder = $this->getConnection()->createQueryBuilder();
        }

        return $this->builder;
    }

    /**
     * @param \Doctrine\DBAL\Query\QueryBuilder $builder
     *
     * @return $this
     */
    public function setBuilder(QueryBuilder $builder)
    {
        $this->builder = $builder;

        return $this;
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
     * @param $provider
     * @param $result
     * @return mixed
     * @throws \Doctrine\DBAL\DBALException
     */
    private function convertTypesToPHPValues(ProviderInterface $provider, $result)
    {
        if(!is_array($result)){
            return $result;
        }
        $fields = $provider->getDefinition()->getFields();

        foreach ($result as $index => $items) {
            foreach ($items as $key => $value) {
                $defaultType = Type::getType(is_numeric($value) ? Type::INTEGER : Type::STRING);
                $type = array_key_exists($key, $fields) ? $fields[$key]->getType() : $defaultType;
                $result[$index][$key] = $type->convertToPHPValue(
                    $value, $this->getConnection()->getDatabasePlatform()
                );
            }
        }

        return $result;
    }

    /**
     * Add a value for column on update or insert statement
     *
     * @param $column
     * @param $value
     *
     * @return $this
     */
    public function addColumnValue($column, $value){
        switch($this->getType()){
            case QueryBuilder::INSERT:
                $this->setValue($column, $value);
                break;
            case QueryBuilder::UPDATE:
                $this->set($column, $value);
                break;
        }

        return $this;
    }

}
