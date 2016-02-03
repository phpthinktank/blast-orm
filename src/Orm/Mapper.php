<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 25.11.2015
 * Time: 17:39
 */

namespace Blast\Db\Orm;

use Blast\Db\Entity\EntityInterface;
use Blast\Db\Entity\Manager;
use Blast\Db\Events\ResultEvent;
use Doctrine\DBAL\Query\QueryBuilder;

class Mapper implements MapperInterface
{

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection = NULL;

    /**
     * @var Factory
     */
    private $factory = NULL;

    /**
     * @var EntityInterface
     */
    private $entity;

    /**
     * @var Manager
     */
    private $manager;


    /**
     * Create mapper for entity
     * @param $entity
     */
    public function __construct($entity)
    {
        $this->entity = $entity;
    }

    /**
     * Get connection.
     *
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection()
    {
        if ($this->connection === null) {
            $this->connection = $this->factory->getConfig()->getConnection();
        }
        return $this->connection;
    }

    /**
     * Set connection by name for mapper
     *
     * @param string $name
     */
    public function setConnection($name)
    {
        $this->connection = $this->factory->getConfig()->getConnection($name);
    }

    /**
     * @return Factory
     */
    public function getFactory()
    {
        if ($this->factory === null) {
            $this->factory = Factory::getInstance();
        }
        return $this->factory;
    }

    /**
     * @param Factory $factory
     */
    public function setFactory($factory)
    {
        $this->factory = $factory;
    }

    /**
     * @return EntityInterface
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->getConnection()->createQueryBuilder();
    }

    /**
     * @return Manager
     */
    public function getManager()
    {
        if ($this->manager === null) {
            $this->manager = new Manager($this->getEntity(), $this, $this->getFactory());
        }
        return $this->manager;
    }

    /**
     * @param Manager $manager
     */
    public function setManager($manager)
    {
        $this->manager = $manager;
    }

    /**
     * Find data by primary key. Optional execute callback for statement.
     *
     * @param $pk
     * @param callable $callback
     * @return array
     */
    public function find($pk, callable $callback = null)
    {
        $field = array_shift($this->getEntity()->getTable()->getPrimaryKey()->getColumns());

        return $this->findBy($field, $pk, $callback = null);
    }

    /**
     * @param $field
     * @param $value
     * @param callable $callback
     * @return EntityInterface|\Blast\Db\Entity\EntityInterface[]
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function findBy($field, $value, callable $callback = null)
    {
        $builder = $this->getQueryBuilder();
        $statement = $builder->select('*');
        $statement->from($this->getEntity()->getTable())
            ->where($builder->expr()->eq($field, $statement->createPositionalParameter($value, $this->getEntity()->getTable()->getColumn($field)->getType())));

        call_user_func($callback, $statement, $builder);

        return $this->fetch($statement);
    }

    /**
     *
     * Get first Result by pk
     *
     * @param $pk
     * @return array
     */
    public function first($pk)
    {
        return $this->find($pk, function (QueryBuilder $statement) {
            $statement->setMaxResults(1)
                ->setFirstResult(0);
        });
    }

    /**
     * Get first result by field and value
     *
     * @param $field
     * @param $value
     * @return EntityInterface|\Blast\Db\Entity\EntityInterface[]
     */
    public function firstBy($field, $value)
    {
        return $this->findBy($field, $value, function (QueryBuilder $statement) {
            $statement->setMaxResults(1)
                ->setFirstResult(0);
        });
    }

    /**
     * Fetch data for entity. if raw is true, fetch assoc instead of entity
     * @param QueryBuilder $statement
     * @param bool $raw
     * @return array
     */
    public function fetch(QueryBuilder $statement, $raw = FALSE)
    {
        $result = $this->getConnection()->executeQuery($statement->getSQL(), $statement->getParameters())->fetchAll();

        return $raw === TRUE ? $result : $this->determineResultSet($result);
    }

    /**
     * @param EntityInterface|EntityInterface[] $entity
     * @return int
     * @internal param $data
     */
    public function create($entity)
    {
        if (is_array($entity)) {
            return $this->batchOperation(__FUNCTION__, $entity);
        }
        $manager = $this->getManager();
        $entity = $manager->create($entity);

        $event = $entity->getEmitter()->emit($entity::BEFORE_CREATE, $entity);

        if ($event->isPropagationStopped()) {
            return false;
        }

        $result = $this->getConnection()->insert($entity->getTable()->getName(), $entity->getData(), $entity->getTable()->getColumnsTypes());

        $event = $entity->getEmitter()->emit(new ResultEvent($entity::AFTER_CREATE, $result), $entity);
        return $event->isPropagationStopped() ? false : $result;
    }

    /**
     * @param EntityInterface|EntityInterface[] $entity
     * @return int
     */
    public function update($entity)
    {
        if (is_array($entity)) {
            return $this->batchOperation(__FUNCTION__, $entity);
        }
        $manager = $this->getManager();
        $entity = $manager->create($entity);

        $event = $entity->getEmitter()->emit($entity::BEFORE_UPDATE, $entity);

        if ($event->isPropagationStopped()) {
            return false;
        }

        $pkName = $entity->getTable()->getPrimaryKeyName();
        $builder = $this->getQueryBuilder();
        $statement = $builder->update($entity->getTable()->getName());

        foreach($entity->getUpdatedData() as $key => $value){
            $statement->set($key, $statement->createPositionalParameter($value, $entity->getTable()->getColumn($key)->getType()));
        }

        $statement->where($builder->expr()->eq($pkName, $entity->get($pkName)));

        $result = $this->getConnection()->executeUpdate($statement->getSQL(), $statement->getParameters(), $statement->getParameterTypes());

        $event = $entity->getEmitter()->emit(new ResultEvent($entity::AFTER_UPDATE, $result), $entity);
        return $event->isPropagationStopped() ? false : $result;
    }

    /**
     * @param EntityInterface|EntityInterface[] $entity
     * @return int
     */
    public function delete($entity)
    {
        if (is_array($entity)) {
            return $this->batchOperation(__FUNCTION__, $entity);
        }

        $manager = $this->getManager();
        $entity = $manager->create($entity);

        $event = $entity->getEmitter()->emit($entity::BEFORE_DELETE, $entity);

        if ($event->isPropagationStopped()) {
            return false;
        }

        $pkName = $entity->getTable()->getPrimaryKeyName();

        $builder = $this->getQueryBuilder();
        $statement = $builder->delete($entity->getTable()->getName())
            ->where($builder->expr()->eq($pkName, $builder->createPositionalParameter($entity->__get($pkName), $entity->getTable()->getColumn($pkName)->getType())));

        $result = $this->getConnection()->executeUpdate($statement->getSQL(), $statement->getParameters(), $statement->getParameterTypes());

        $event = $entity->getEmitter()->emit(new ResultEvent($entity::AFTER_DELETE, $result), $entity);
        return $event->isPropagationStopped() ? false : $result;
    }

    /**
     * @param EntityInterface|EntityInterface[] $entity
     * @return int
     */
    public function save($entity)
    {
        if (is_array($entity)) {
            return $this->batchOperation(__FUNCTION__, $entity);
        }

        //may be it is better to start an transaction
        //save all relations before saving entity
        $relations = $entity->getRelations();

        if (count($relations) > 0) {
            foreach ($relations as $relation) {
                $relation->save();
            }
        }

        return $entity->isNew() ? $this->create($entity) : $this->update($entity);
    }

    /**
     * Analyse result and return correct set
     * @param $data
     * @return EntityInterface|EntityInterface[]|null
     */
    protected function determineResultSet($data)
    {
        $count = count($data);
        $result = NULL;

        if ($count > 1) { //if result set has many items, return a collection of entities
            $result = [];
            foreach ($data as $item) {
                $result[] = $this->getManager()->create()->setData($item);
            }
        } elseif ($count === 1) { //if result has one item, return the entity
            $result = $this->getManager()->create()->setData(array_shift($data));
        }

        return $result;
    }

    /**
     * Execute batch delete, save, update or insert
     * @param $operation
     * @param $entity
     * @return array
     */
    private function batchOperation($operation, $entity)
    {
        $results = [];
        foreach ($entity as $_) {
            $this->{$operation}($_);
        }
        return $results;
    }

}