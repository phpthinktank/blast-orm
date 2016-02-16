<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 25.11.2015
 * Time: 17:39
 */

namespace Blast\Db\Orm;

use Blast\Db\Orm\Model\ModelAwareInterface;
use Blast\Db\Orm\Model\ModelManager;
use Blast\Db\Orm\Model\ModelInterface;
use Blast\Db\ConnectionAwareTrait;
use Blast\Db\ManagerAwareTrait;
use Blast\Db\Orm\Relations\RelationInterface;
use Blast\Db\Orm\Relations\RelationTrait;
use Blast\Db\Query\Query;
use Blast\Db\Orm\Relations\RelationAwareInterface;

/**
 * Class Mapper
 *
 * Mapping results to entities
 *
 * @package Blast\Db\Orm
 */
class Mapper implements MapperInterface, ModelAwareInterface, RelationInterface
{

    use ManagerAwareTrait;
    use ConnectionAwareTrait;
    use RelationTrait;

    /**
     * @var ModelInterface
     */
    private $model;


    /**
     * Create mapper for Model
     * @param ModelInterface
     * @return $this
     */
    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @return ModelInterface
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Create a new Query instance
     * @return Query
     */
    public function createQuery()
    {
        return new Query($this->getModel(), $this->getConnection()->createQueryBuilder());
    }

    /**
     * Find result by field or primary key
     *
     * @param mixed $value
     * @param null $field
     * @return ModelInterface
     */
    public function find($value, $field = null)
    {
        $field = $this->getModel()->getTable()->getPrimaryKeyName();
        $query = $this->select();
        if (isset($field) && isset($value)) {
            $query->where($query->expr()->eq($field, $query->createPositionalParameter($value, $this->getModel()->getTable()->getColumn($field)->getType())));
        }

        return $query->execute(Query::RESULT_Model);
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
            ->from($this->getModel()->getTable()->getName());

        return $query;
    }

    /**
     * Create a new Model or a collection of entities in storage.
     *
     * @param ModelInterface|ModelInterface[]|CollectionInterface $model
     * @return int|int[]|bool[]|bool
     */
    public function create($model)
    {
        //execute batch if condition matches
        if ($this->isMassProcessable($model)) {
            return $this->massProcess(__FUNCTION__, $model);
        }

        //save relations before save Model
        $this->saveRelations($model);

        //prepare statement
        $query = $this->createQuery();
        $query->insert($model->getTable()->getName());

        foreach ($model->getData() as $key => $value) {
            $query->setValue($key, $query->createPositionalParameter($value, $model->getTable()->getColumn($key)->getType()));
        }

        return $query->execute();
    }

    /**
     * Update an existing Model or a collection of entities in storage
     *
     * Returns false on error and 0 when nothing has been updated!
     *
     * Optional force update of entities without updates
     *
     * @param ModelInterface|ModelInterface[]|CollectionInterface $model
     * @param bool $forceUpdate
     * @return int|int[]|bool[]|bool
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function update($model, $forceUpdate = false)
    {
        //execute batch if condition matches
        if ($this->isMassProcessable($model)) {
            return $this->massProcess(__FUNCTION__, $model);
        }

        //save relations before save Model
        $this->saveRelations($model);

        if(!$model->isUpdated()){
            return 0;
        }

        //prepare statement
        $pkName = $model->getTable()->getPrimaryKeyName();
        $query = $this->createQuery();
        $query->update($model->getTable()->getName());

        foreach ($model->getUpdatedData() as $key => $value) {
            $query->set($key, $query->createPositionalParameter($value, $model->getTable()->getColumn($key)->getType()));
        }

        $query->where($query->expr()->eq($pkName, $model->get($pkName)));

        return $query->execute();
    }

    /**
     * Delete an existing Model or a collection of entities in storage
     *
     * @param ModelInterface|ModelInterface[]|CollectionInterface $model
     * @return int
     */
    public function delete($model)
    {
        //prepare for batch
        //delete will always batch delete
        $entities = [$model];

        if ($this->isMassProcessable($model)) {
            $entities = $model instanceof CollectionInterface ? $model->getData() : $model;
            $model = array_shift($model);
        }

        //prepare statement
        $pkName = $model->getTable()->getPrimaryKeyName();
        $query = $this->createQuery();
        $query->delete($model->getTable()->getName());

        //add entities by pk to delete
        foreach ($entities as $instance) {
            $instance = $this->prepareModel($instance);
            $query->orWhere($query->expr()->eq($pkName, $query->createPositionalParameter($instance->__get($pkName), $instance->getTable()->getColumn($pkName)->getType())));
        }

        return $query->execute();
    }

    /**
     * Create or update an Model or a collection of entities in storage
     *
     * Optional force update of entities without updates
     *
     * @param ModelInterface|ModelInterface[]|array $model
     * @param bool $forceUpdate
     * @return int
     */
    public function save($model, $forceUpdate = false)
    {
        if ($this->isMassProcessable($model)) {
            return $this->massProcess(__FUNCTION__, $model);
        }

        return $model->isNew() ? $this->create($model) : $this->update($model);
    }

    /**
     * Save relations for a specific Model
     *
     * @param ModelInterface $model
     */
    protected function saveRelations($model)
    {
        if(!($model instanceof RelationAwareInterface)){
            return;
        }
        //maybe it is better to start an transaction
        //save all relations before saving Model
        $relations = $model->getRelations();

        if (count($relations) > 0) {
            foreach ($relations as $relation) {
                $relation->save();
            }
        }
    }

    /**
     * Check if Model is mass processable
     *
     * @param $model
     * @return bool
     */
    protected function isMassProcessable($model)
    {
        return $model instanceof CollectionInterface || is_array($model) || $model instanceof \ArrayObject;
    }

    /**
     * Execute mass save, update or insert
     *
     * @param $operation
     * @param $model
     * @return array
     */
    protected function massProcess($operation, $model)
    {
        $results = [];
        foreach ($model as $_) {
            $results[] = $this->{$operation}($_);
        }

        return $results;
    }
}