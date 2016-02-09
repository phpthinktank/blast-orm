<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 25.11.2015
 * Time: 17:39
 */

namespace Blast\Db\Orm;

use Blast\Db\Entity\CollectionInterface;
use Blast\Db\Entity\EntityInterface;
use Blast\Db\Entity\Manager;
use Blast\Db\Entity\ManagerInterface;
use Blast\Db\Events\ResultEvent;
use Blast\Db\ConnectionAwareTrait;
use Blast\Db\FactoryAwareTrait;
use Blast\Db\Query;

/**
 * Class Mapper
 *
 * Mapping results to entities
 *
 * @package Blast\Db\Orm
 */
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
     * @param EntityInterface|ManagerInterface $entity
     */
    public function __construct($entity)
    {
        $this->manager = $entity instanceof ManagerInterface ? $entity : $this->createManager($entity);
        $this->entity = $this->getManager()->getEntity()->setMapper($this);
    }

    /**
     * Change connection by name for mapper
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
     * @return Manager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Create a new Query instance
     * @return Query
     */
    public function createQuery()
    {
        return new Query($this->getConnection()->createQueryBuilder(), $this->getManager());
    }

    /**
     * Find result by primary key
     *
     * @param $value
     * @return array|Query
     * @throws \Doctrine\DBAL\Schema\SchemaException
     * @internal param null $pk
     */
    public function find($value)
    {
        $field = $this->getEntity()->getTable()->getPrimaryKeyName();
        $query = $this->select();
        if (isset($field) && isset($value)) {
            $query->where($query->expr()->eq($field, $query->createPositionalParameter($value, $this->getEntity()->getTable()->getColumn($field)->getType())));
        }

        return $query->execute();
    }

    /**
     * Get a statement and build a query. Table is already selected
     * @param array $selects
     * @return Query
     */
    public function select($selects = ['*'])
    {
        $query = $this->createQuery();
        $query->select($selects)
            ->from($this->getEntity()->getTable());

        return $query;
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
        if ($this->isMassProcessable($entity)) {
            return $this->massProcess(__FUNCTION__, $entity);
        }

        //prepare entity
        $entity = $this->prepareEntity($entity);

        //save relations before save entity
        $this->saveRelations($entity);

        //emit before event
        if ($entity->getEmitter()->emit($entity::BEFORE_CREATE, $entity)->isPropagationStopped()) {
            return FALSE;
        }

        //prepare statement
        $query = $this->createQuery();
        $query->insert($entity->getTable()->getName());

        foreach ($entity->getData() as $key => $value) {
            $query->setValue($key, $query->createPositionalParameter($value, $entity->getTable()->getColumn($key)->getType()));
        }

        //execute statement and emit after event
        $event = $entity->getEmitter()->emit(new ResultEvent($entity::AFTER_CREATE, $query->execute()), $entity);

        return $event->isPropagationStopped() ? FALSE : $event->getResult();
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
        if ($this->isMassProcessable($entity)) {
            return $this->massProcess(__FUNCTION__, $entity);
        }

        //prepare entity
        $entity = $this->prepareEntity($entity);

        //save relations before save entity
        $this->saveRelations($entity);

        if ($entity->getEmitter()->emit($entity::BEFORE_UPDATE, $entity)->isPropagationStopped()) {
            return FALSE;
        }

        //prepare statement
        $pkName = $entity->getTable()->getPrimaryKeyName();
        $query = $this->createQuery();
        $query->update($entity->getTable()->getName());

        foreach ($entity->getUpdatedData() as $key => $value) {
            $query->set($key, $query->createPositionalParameter($value, $entity->getTable()->getColumn($key)->getType()));
        }

        $query->where($query->expr()->eq($pkName, $entity->get($pkName)));

        //execute statement and emit after event
        $event = $entity
            ->getEmitter()
            ->emit(new ResultEvent($entity::AFTER_UPDATE, $query->execute()), $entity);

        return $event->isPropagationStopped() ? FALSE : $event->getResult();
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

        if ($this->isMassProcessable($entity)) {
            $entities = $entity instanceof CollectionInterface ? $entity->getData() : $entity;
            $entity = array_shift($entity);
        }

        //prepare entity
        $entity = $this->prepareEntity($entity);

        //emit before event
        if ($entity->getEmitter()->emit($entity::BEFORE_DELETE, $entity)->isPropagationStopped()) {
            return FALSE;
        }

        //prepare statement
        $pkName = $entity->getTable()->getPrimaryKeyName();
        $query = $this->createQuery();
        $query->delete($entity->getTable()->getName());

        //add entities by pk to delete
        foreach ($entities as $instance) {
            $instance = $this->prepareEntity($instance);
            $query->orWhere($query->expr()->eq($pkName, $query->createPositionalParameter($instance->__get($pkName), $instance->getTable()->getColumn($pkName)->getType())));
        }

        //execute statement and emit after event
        $event = $entity
            ->getEmitter()
            ->emit(new ResultEvent($entity::AFTER_DELETE, $query->execute()), $entity, $entities);
        $result = $event->isPropagationStopped() ? FALSE : $event->getResult();

        return $result;
    }

    /**
     * Create or update an entity or a collection of entities in storage
     *
     * @param EntityInterface|EntityInterface[]|array $entity
     * @return int
     */
    public function save($entity)
    {
        if (is_array($entity)) {
            return $this->massProcess(__FUNCTION__, $entity);
        }

        return $entity->isNew() ? $this->create($entity) : $this->update($entity);
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
     * @param EntityInterface $entity
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

    /**
     * Check if entity is mass processable
     *
     * @param $entity
     * @return bool
     */
    protected function isMassProcessable($entity)
    {
        return $entity instanceof CollectionInterface || is_array($entity) || $entity instanceof \ArrayObject;
    }

    /**
     * Execute mass save, update or insert
     *
     * @param $operation
     * @param $entity
     * @return array
     */
    protected function massProcess($operation, $entity)
    {
        $results = [];
        foreach ($entity as $_) {
            $results[] = $this->{$operation}($_);
        }

        return $results;
    }

    /**
     * @param $entity
     * @return ManagerInterface
     */
    protected function createManager($entity)
    {
        $managerConcrete = $this->getFactory()->getContainer()->get(ManagerInterface::class);

        return (new \ReflectionClass($managerConcrete))->newInstanceArgs([$entity]);
    }
}