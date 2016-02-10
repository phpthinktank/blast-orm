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
use Blast\Db\Entity\ManagerInterface;
use Blast\Db\Events\ResultEvent;
use Blast\Db\ConnectionAwareTrait;
use Blast\Db\Factory;
use Blast\Db\FactoryAwareTrait;
use Blast\Db\Query;
use Blast\Db\Relations\RelationManagerInterface;

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
     * Create mapper for entity
     * @param EntityInterface
     */
    public function __construct($entity)
    {
        $this->entity = $entity;
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
     * Create a new Query instance
     * @return Query
     */
    public function createQuery()
    {
        return new Query($this->getEntity(), $this->getConnection()->createQueryBuilder());
    }

    /**
     * Find result by primary key
     *
     * @param $value
     * @return EntityInterface
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function find($value)
    {
        $field = $this->getEntity()->getTable()->getPrimaryKeyName();
        $query = $this->select();
        if (isset($field) && isset($value)) {
            $query->where($query->expr()->eq($field, $query->createPositionalParameter($value, $this->getEntity()->getTable()->getColumn($field)->getType())));
        }

        return $query->execute(Query::RESULT_ENTITY);
    }

    /**
     * Get a collection of all entities
     *
     * @return array|CollectionInterface
     */
    public function all(){
        return $this->select()->execute(Query::RESULT_COLLECTION);
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
            ->from($this->getEntity()->getTable()->getName());

        return $query;
    }

    /**
     * Create a new entity or a collection of entities in storage.
     *
     * @param EntityInterface|EntityInterface[]|CollectionInterface $entity
     * @return int|int[]|bool[]|bool
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
     * Returns false on error and 0 when nothing has been updated!
     *
     * Optional force update of entities without updates
     *
     * @param EntityInterface|EntityInterface[]|CollectionInterface $entity
     * @param bool $forceUpdate
     * @return int|int[]|bool[]|bool
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function update($entity, $forceUpdate = false)
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

        if(!$entity->isUpdated()){
            return 0;
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
     * @param EntityInterface|EntityInterface[]|CollectionInterface $entity
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
     * Optional force update of entities without updates
     *
     * @param EntityInterface|EntityInterface[]|array $entity
     * @param bool $forceUpdate
     * @return int
     */
    public function save($entity, $forceUpdate = false)
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
        $targetEntity = get_class($this->getEntity());
        if(!is_subclass_of($entity, $targetEntity)){
            throw new \InvalidArgumentException('Given entity needs to be an instance of ' . $targetEntity);
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
        if(!($entity instanceof RelationManagerInterface)){
            return;
        }
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
}