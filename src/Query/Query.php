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


use Blast\Db\Entity\CollectionInterface;
use Blast\Db\Entity\EntityInterface;
use Blast\Db\ConnectionAwareTrait;
use Blast\Db\Events\BuilderEvent;
use Blast\Db\Events\ResultEvent;
use Blast\Db\Manager;
use Blast\Db\ManagerAwareTrait;
use Blast\Db\Orm\Model\ModelInterface;
use Doctrine\DBAL\Query\QueryBuilder;
use League\Event\EmitterAwareInterface;
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
     * @var QueryBuilder
     */
    private $builder;

    /**
     * @var ModelInterface|array|stdClass|\ArrayObject
     */
    private $model;

    /**
     * Statement constructor.
     * @param ModelInterface|array|stdClass|\ArrayObject $model
     * @param Query $builder
     */
    public function __construct($model = null, $builder = null)
    {
        $this->builder = $builder === null ? Manager::getInstance()->getConfig()->getConnection()->createQueryBuilder() : $builder;
        $this->model = $model;
    }

    /**
     * @return QueryBuilder
     */
    public function getBuilder()
    {
        return $this->builder;
    }

    /**
     * @return ModelInterface|array|stdClass|\ArrayObject
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Fetch data for entity. if raw is true, fetch assoc instead of entity
     *
     * @param string $convert
     * @return array|Result|ResultCollection
     * @throws \Doctrine\DBAL\DBALException
     */
    public function execute($convert = 'auto')
    {
        $model = $this->getModel();

        $builder = $this->beforeExecute($model, $this->getBuilder());

        if(!$builder){
            return false;
        }

        $isSelect = $builder->getType() === $builder::SELECT;
        $statement = $builder->execute();

        $result = $this->afterExecute($isSelect ? $statement->fetchAll() : $statement, $model, $builder);

        if(!$result){
            return false;
        }

        $decorator = new ResultDecorator($result, $model);

        return $decorator->decorate($convert);
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

    /**
     * @param $model
     * @param $builder
     * @return QueryBuilder
     */
    private function beforeExecute($model, $builder)
    {
        if ($model instanceof EmitterAwareInterface) {
            $event = $model->getEmitter()->emit(new BuilderEvent('before.' . $this->getType(), $builder));
            if ($event->isPropagationStopped()) {
                return false;
            }

            if ($event instanceof BuilderEvent) {
                $builder = $event->getBuilder();
            }
        }

        return $builder;
    }

    /**
     * @param $result
     * @param $model
     * @param Query|QueryBuilder $builder
     * @return array
     */
    private function afterExecute($result, $model, $builder)
    {
        if ($model instanceof EmitterAwareInterface) {
            $event = $model->getEmitter()->emit(new ResultDecorator('after.' . $builder->getType(), $result), $builder);
            if ($event->isPropagationStopped()) {
                return false;
            }

            if ($event instanceof ResultEvent) {
                $result = $event->getResult();
            }
        }

        return $result;
    }

}