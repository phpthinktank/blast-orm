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
use Blast\Db\Entity\ManagerInterface;
use Blast\Db\Events\ResultEvent;
use Blast\Db\Orm\Traits\ConnectionAwareTrait;
use Blast\Db\Orm\Traits\FactoryAwareTrait;
use Doctrine\DBAL\Query\QueryBuilder;

class Mapper implements MapperInterface
{

    use FactoryAwareTrait;
    use ConnectionAwareTrait;

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
     * Set connection by name for mapper
     *
     * @param string $name
     */
    public function onConnection($name)
    {
        $this->connection = $this->factory->getConfig()->getConnection($name);
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
            $managerConcrete = $this->getFactory()->getContainer()->get(ManagerInterface::class);
            $this->manager = (new \ReflectionClass($managerConcrete))->newInstanceArgs([$this->getEntity(), $this, $this->getFactory()]);
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
     * Find data by field and value. Optional execute callback for statement.
     *
     * @param $field
     * @param $value
     * @param callable $callback
     * @return EntityInterface|\Blast\Db\Entity\EntityInterface[]
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function findBy($field, $value, callable $callback = null)
    {
        $builder = $this->getQueryBuilder()->select('*');
        $builder->from($this->getEntity()->getTable())
            ->where($builder->expr()->eq($field, $builder->createPositionalParameter($value, $this->getEntity()->getTable()->getColumn($field)->getType())));

        call_user_func($callback, $builder);

        return $this->fetch($builder);
    }

    /**
     * Get first Result by pk
     *
     * @param $pk
     * @return array
     */
    public function first($pk)
    {
        return $this->find($pk, function (QueryBuilder $builder) {
            $builder->setMaxResults(1)
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
        return $this->findBy($field, $value, function (QueryBuilder $builder) {
            $builder->setMaxResults(1)
                ->setFirstResult(0);
        });
    }

    /**
     * Fetch data for entity. if raw is true, fetch assoc instead of entity
     *
     * @param QueryBuilder $builder
     * @param bool $raw
     * @return array
     */
    public function fetch(QueryBuilder $builder, $raw = FALSE)
    {
        $result = $this->getConnection()->executeQuery($builder->getSQL(), $builder->getParameters())->fetchAll();

        return $raw === TRUE ? $result : $this->determineResultSet($result);
    }

    /**
     * Create a new entity or a collection of entities in storage.
     *
     * @param EntityInterface|EntityInterface[] $entity
     * @return int
     * @internal param $data
     */
    public function create($entity)
    {
        //execute batch if condition matches
        if (is_array($entity)) {
            return $this->batchOperation(__FUNCTION__, $entity);
        }

        //prepare entity
        $entity = $this->prepareEntity($entity);

        //save relations before save entity
        $this->saveRelations($entity);

        //emit before event
        if ($entity->getEmitter()->emit($entity::BEFORE_CREATE, $entity)->isPropagationStopped()) {
            return false;
        }

        //prepare statement
        $builder = $this->getQueryBuilder()->insert($entity->getTable()->getName());

        foreach ($entity->getData() as $key => $value) {
            $builder->setValue($key, $builder->createPositionalParameter($value, $entity->getTable()->getColumn($key)->getType()));
        }

        //execute statement and emit after event
        $event = $entity->getEmitter()->emit(new ResultEvent($entity::AFTER_CREATE, $this->executeUpdate($builder)), $entity);
        return $event->isPropagationStopped() ? false : $event->getResult();
    }

    /**
     * Update an existing entity or a collection of entities in storage
     *
     * @param EntityInterface|EntityInterface[] $entity
     * @return int
     */
    public function update($entity)
    {
        //execute batch if condition matches
        if (is_array($entity)) {
            return $this->batchOperation(__FUNCTION__, $entity);
        }

        //prepare entity
        $entity = $this->prepareEntity($entity);

        //save relations before save entity
        $this->saveRelations($entity);

        if ($entity->getEmitter()->emit($entity::BEFORE_UPDATE, $entity)->isPropagationStopped()) {
            return false;
        }

        //prepare statement
        $pkName = $entity->getTable()->getPrimaryKeyName();
        $builder = $this->getQueryBuilder()->update($entity->getTable()->getName());

        foreach ($entity->getUpdatedData() as $key => $value) {
            $builder->set($key, $builder->createPositionalParameter($value, $entity->getTable()->getColumn($key)->getType()));
        }

        $builder->where($builder->expr()->eq($pkName, $entity->get($pkName)));

        //execute statement and emit after event
        $event = $entity
            ->getEmitter()
            ->emit(new ResultEvent($entity::AFTER_UPDATE, $this->executeUpdate($builder)), $entity);
        return $event->isPropagationStopped() ? false : $event->getResult();
    }

    /**
     * Delete an existing entity or a collection of entities in storage
     * 
     * @param EntityInterface|EntityInterface[] $entity
     * @return int
     */
    public function delete($entity)
    {
        //prepare for batch
        //delete will always batch delete
        $entities = [$entity];

        if (is_array($entity)) {
            $entities = $entity;
            $entity = array_shift($entity);
        }

        //prepare entity
        $entity = $this->prepareEntity($entity);

        //emit before event
        if ($entity->getEmitter()->emit($entity::BEFORE_DELETE, $entity)->isPropagationStopped()) {
            return false;
        }

        //prepare statement
        $pkName = $entity->getTable()->getPrimaryKeyName();
        $builder = $this->getQueryBuilder()->delete($entity->getTable()->getName());

        //add entities by pk to delete
        foreach ($entities as $instance) {
            $instance = $this->prepareEntity($instance);
            $builder->orWhere($builder->expr()->eq($pkName, $builder->createPositionalParameter($instance->__get($pkName), $instance->getTable()->getColumn($pkName)->getType())));
        }

        //execute statement and emit after event
        $event = $entity
            ->getEmitter()
            ->emit(new ResultEvent($entity::AFTER_DELETE, $this->executeUpdate($builder)), $entity, $entities);
        $result = $event->isPropagationStopped() ? false : $event->getResult();
        return $result;
    }

    /**
     * Create or update an entity or a collection of entities in storage
     * 
     * @param EntityInterface|EntityInterface[] $entity
     * @return int
     */
    public function save($entity)
    {
        if (is_array($entity)) {
            return $this->batchOperation(__FUNCTION__, $entity);
        }

        return $entity->isNew() ? $this->create($entity) : $this->update($entity);
    }

    /**
     * Analyse result and return one or many results
     * 
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
     * Execute batch save, update or insert
     * 
     * @param $operation
     * @param $entity
     * @return array
     */
    private function batchOperation($operation, $entity)
    {
        $results = [];
        foreach ($entity as $_) {
            $results[] = $this->{$operation}($_);
        }
        return $results;
    }

    /**
     * @param $builder
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    public function executeUpdate(QueryBuilder $builder)
    {
        return $this->getConnection()->executeUpdate($builder->getSQL(), $builder->getParameters(), $builder->getParameterTypes());
    }

    /**
     * @param $entity
     * @return EntityInterface
     */
    protected function prepareEntity($entity)
    {
        $manager = $this->getManager();
        $entity = $manager->create($entity);

        if ($entity != $this->getEntity()) {
            throw new \InvalidArgumentException('Given entity needs to be an instance of ' . get_class($this->getEntity()));
        }
        return $entity;
    }

    /**
     * Save relations for a specific entity
     *
     * @param $entity
     */
    protected function saveRelations($entity)
    {
        //maybe it is better to start an transaction
        //save all relations before saving entity
        $relations = $entity->getRelations();

        if (count($relations) > 0) {
            foreach ($relations as $relation) {
                $relation->save();
            }
        }
    }
}