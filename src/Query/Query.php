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

namespace Blast\Db\Query;


use Blast\Db\Entity\Collection;
use Blast\Db\Entity\CollectionInterface;
use Blast\Db\Entity\EntityInterface;
use Blast\Db\ConnectionAwareTrait;
use Blast\Db\ManagerAwareTrait;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Query\QueryBuilder;
use stdClass;

/**
 * Class Statement
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
 * @method Query select($select = null)
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
class Query
{

    use ManagerAwareTrait;
    use ConnectionAwareTrait;

    /**
     *
     */
    const RESULT_COLLECTION = 'collection';
    /**
     *
     */
    const RESULT_ENTITY = 'entity';
    const RESULT_RAW = 'raw';
    /**
     *
     */
    const RESULT_AUTO = 'auto';

    /**
     * @var QueryBuilder
     */
    private $builder;

    /**
     * @var EntityInterface
     */
    private $entity;

    /**
     * Statement constructor.
     * @param EntityInterface|array|stdClass|\ArrayObject $entity
     * @param QueryBuilder $builder
     */
    public function __construct($entity = null, QueryBuilder $builder = null)
    {
        $this->builder = $builder === null ? Factory::getInstance()->getConfig()->getConnection()->createQueryBuilder() : $builder;
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
     * @return EntityInterface
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Fetch data for entity. if raw is true, fetch assoc instead of entity
     *
     * @param string $convert
     * @return array|CollectionInterface|EntityInterface
     * @throws \Doctrine\DBAL\DBALException
     */
    public function execute($convert = self::RESULT_AUTO)
    {
        $builder = $this->getBuilder();
        $isFetchable = $builder->getType() === $builder::SELECT;
        $statement = $builder->execute();
        $result = $isFetchable ? $statement->fetchAll() : $statement;

        return $convert === self::RESULT_RAW || $this->getEntity() === null || $result instanceof Statement || is_int($result) ? $result : $this->determineResultSet($result, $convert);
    }

    /**
     * Determine result and return one or many results
     *
     * @param $data
     * @param string $convert
     * @return CollectionInterface|EntityInterface|null
     */
    protected function determineResultSet($data, $convert = self::RESULT_AUTO)
    {
        $count = count($data);
        $result = NULL;

        if ($count > 1 || $convert === static::RESULT_COLLECTION) { //if result set has many items, return a collection of entities
            foreach ($data as $key => $value) {
                /**
                 * @todo support deprecated entity data passing
                 */
                if($this->getEntity() instanceof EntityInterface){
                    $entity = clone $this->getEntity();
                }else{
                    $entity = new Result();
                }
                $data[$key] = $entity->setData($value);
            }
            $result = $this->getEntity() instanceof EntityInterface ? new Collection($data) : new ResultCollection();
        } elseif ($count === 1 || $convert === static::RESULT_ENTITY) { //if result has one item, return the entity
            if($this->getEntity() instanceof EntityInterface) {
                $entity = clone $this->getEntity();
            }else{
                $entity = new Result();
            }
            $result = $entity->setData(array_shift($data));
        }

        return $result;
    }

    /**
     * Magic call of builder methods
     *
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $result = call_user_func_array([$this->getBuilder(), $name], $arguments);
        return $result instanceof QueryBuilder ? $this : $result;
    }

}