<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 25.11.2015
 * Time: 17:39
 */

namespace Blast\Db\Orm;

use Blast\Db\Data\DataHelper;
use Blast\Db\Data\UpdatedDataObjectInterface;
use Blast\Db\Orm\Model\ModelAwareInterface;
use Blast\Db\Orm\Model\ModelAwareTrait;
use Blast\Db\Orm\Model\ModelInterface;
use Blast\Db\ConnectionAwareTrait;
use Blast\Db\ManagerAwareTrait;
use Blast\Db\Orm\Relations\RelationInterface;
use Blast\Db\Orm\Relations\RelationTrait;
use Blast\Db\Query\Query;
use Blast\Db\Orm\Relations\RelationAwareInterface;
use Blast\Db\Query\ResultCollection;
use Blast\Db\Query\ResultDecorator;

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
    use ModelAwareTrait;

    /**
     * Disable direct access to mapper
     * @param $model
     */
    public function __construct($model)
    {
        $this->setModel($model);
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
    public function find($value, $field = NULL)
    {
        $query = $this->select();
        if (isset($field) && isset($value)) {
            $query->where($query->expr()->eq($field, $query->createPositionalParameter($value)));
        }

        return $query->execute(ResultDecorator::RESULT_ENTITY);
    }

    /**
     * Get a collection of all entities
     *
     * @return ResultCollection
     */
    public function all()
    {
        return $this->select()->execute(ResultDecorator::RESULT_COLLECTION);
    }

    /**
     * Get a statement and build a query. Table is already selected
     * @param array $selects
     * @return Query
     */
    public function select($selects = ['*'])
    {
        $query = $this->createQuery();
        $query->select($selects);

        $entity = $this->getModel();
        $query->from(MapperHelper::findOption('table', $entity));

        return $query;
    }

    /**
     * Create a new Model or a collection of entities in storage.
     *
     * @param ModelInterface|ModelInterface[] $entity
     * @return int|int[]|bool[]|bool
     */
    public function create($entity)
    {
        //execute batch if condition matches
        if ($this->isMassProcessable($entity)) {
            return $this->massProcess(__FUNCTION__, $entity);
        }

        //save relations before save Model
        $this->saveRelations($entity);

        //prepare statement
        $query = $this->createQuery();
        $query->insert(MapperHelper::findOption('table', $entity));

        foreach ($entity->getData() as $key => $value) {
            $query->setValue($key, $query->createPositionalParameter($value));
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
     * @param ModelInterface|ModelInterface[] $entity
     * @return int|int[]|bool[]|bool
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function update($entity)
    {
        //execute batch if condition matches
        if ($this->isMassProcessable($entity)) {
            return $this->massProcess(__FUNCTION__, $entity);
        }

        //save relations before save Model
        $this->saveRelations($entity);

        if (!$entity->isUpdated()) {
            return 0;
        }

        //prepare statement
        $pkName = MapperHelper::findOption('primaryKeyName', $entity);
        $query = $this->createQuery();
        $query->update(MapperHelper::findOption('table', $entity));

        $data = $entity instanceof UpdatedDataObjectInterface ? $entity->getUpdatedData() : DataHelper::receiveDataFromObject($entity);
        foreach ($data as $key => $value) {
            $query->set($key, $query->createPositionalParameter($value));
        }

        $query->where($query->expr()->eq($pkName, $data[$pkName]));

        return $query->execute();
    }

    /**
     * Delete an existing Model or a collection of entities in storage
     *
     * @param ModelInterface|ModelInterface[] $entity
     * @return int
     */
    public function delete($entity)
    {
        //prepare for batch
        //delete will always batch delete
        $entities = [$entity];

        if ($this->isMassProcessable($entity)) {
            $entities = $entity instanceof ResultCollection ? $entity->getData() : $entity;
            $entity = array_shift($entity);
        }

        //prepare statement
        $pkName = MapperHelper::findOption('primaryKeyName', $entity);
        $query = $this->createQuery();
        $query->delete(MapperHelper::findOption('table', $entity));

        //add entities by pk to delete
        foreach ($entities as $instance) {
            $data = DataHelper::receiveDataFromObject($instance);
            $query->orWhere($query->expr()->eq($pkName, $query->createPositionalParameter($data[$pkName])));
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
    public function save($model, $forceUpdate = FALSE)
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
        if (!($model instanceof RelationAwareInterface)) {
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
        return $model instanceof ResultCollection || is_array($model) || $model instanceof \ArrayObject;
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