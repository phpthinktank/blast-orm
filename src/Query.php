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

namespace Blast\Db;


use Blast\Db\Entity\Collection;
use Blast\Db\Entity\CollectionInterface;
use Blast\Db\Entity\EntityInterface;
use Blast\Db\Entity\ManagerInterface;
use Blast\Db\ConnectionAwareTrait;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Class Statement
 *
 * @method \Doctrine\DBAL\Query\Expression\ExpressionBuilder expr()
 * @method int getType()
 * @method int getState()
 * @method string getSQL()
 * @method QueryBuilder setParameter($key, $value, $type = null)
 * @method QueryBuilder setParameters(array $params, array $types = array())
 * @method array getParameters()
 * @method mixed getParameter($key)
 * @method array getParameterTypes()
 * @method mixed getParameterType($key)
 * @method QueryBuilder setFirstResult($firstResult)
 * @method int getFirstResult()
 * @method QueryBuilder setMaxResults($maxResults)
 * @method int getMaxResults()
 * @method QueryBuilder add($sqlPartName, $sqlPart, $append = false)
 * @method QueryBuilder select($select = null)
 * @method QueryBuilder addSelect($select = null)
 * @method QueryBuilder delete($delete = null, $alias = null)
 * @method QueryBuilder update($update = null, $alias = null)
 * @method QueryBuilder insert($insert = null)
 * @method QueryBuilder from($from, $alias = null)
 * @method QueryBuilder join($fromAlias, $join, $alias, $condition = null)
 * @method QueryBuilder innerJoin($fromAlias, $join, $alias, $condition = null)
 * @method QueryBuilder leftJoin($fromAlias, $join, $alias, $condition = null)
 * @method QueryBuilder rightJoin($fromAlias, $join, $alias, $condition = null)
 * @method QueryBuilder set($key, $value)
 * @method QueryBuilder where($predicates)
 * @method QueryBuilder andWhere($where)
 * @method QueryBuilder orWhere($where)
 * @method QueryBuilder groupBy($groupBy)
 * @method QueryBuilder addGroupBy($groupBy)
 * @method QueryBuilder setValue($column, $value)
 * @method QueryBuilder values(array $values)
 * @method QueryBuilder having($having)
 * @method QueryBuilder andHaving($having)
 * @method QueryBuilder orHaving($having)
 * @method QueryBuilder orderBy($sort, $order = null)
 * @method QueryBuilder addOrderBy($sort, $order = null)
 * @method mixed getQueryPart($queryPartName)
 * @method array getQueryParts()
 * @method QueryBuilder resetQueryParts($queryPartNames = null)
 * @method QueryBuilder resetQueryPart($queryPartName)
 * @method string __toString()
 * @method string createNamedParameter($value, $type = \PDO::PARAM_STR, $placeHolder = null)
 * @method string createPositionalParameter($value, $type = \PDO::PARAM_STR)
 * @method void __clone()
 *
 * @package Blast\Db\Orm
 */
class Query
{

    use FactoryAwareTrait;
    use ConnectionAwareTrait;

    /**
     *
     */
    const RESULT_COLLECTION = 'collection';
    /**
     *
     */
    const RESULT_ENTITY = 'entity';
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
     * @param QueryBuilder $builder
     * @param EntityInterface $entity
     */
    public function __construct(QueryBuilder $builder = null, EntityInterface $entity = null)
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
     * @param bool $raw
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function execute($convert = self::RESULT_AUTO, $raw = FALSE)
    {
        $builder = $this->getBuilder();
        $isFetchable = $builder->getType() === $builder::SELECT;
        $statement = $builder->execute();
        $result = $isFetchable ? $statement->fetchAll() : $statement;

        return $raw === TRUE || $this->getEntity() === null || $result instanceof Statement || is_int($result) ? $result : $this->determineResultSet($result, $convert);
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
                $entity = clone $this->getEntity();
                $data[$key] = $entity->setData($value);
            }
            $result = new Collection($data);
        } elseif ($count === 1 || $convert === static::RESULT_ENTITY) { //if result has one item, return the entity
            $entity = clone $this->getEntity();
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