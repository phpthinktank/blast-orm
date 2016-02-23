<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 25.11.2015
 * Time: 17:39
 */

namespace Blast\Orm\Mapper;

use ArrayObject;
use Blast\Orm\Data\DataHelper;
use Blast\Orm\Data\DataObject;
use Blast\Orm\Data\DataObjectInterface;
use Blast\Orm\Data\UpdatedDataObjectInterface;
use Blast\Orm\EntityAwareInterface;
use Blast\Orm\EntityAwareTrait;
use Blast\Orm\Manager;
use Blast\Orm\Query;
use Blast\Orm\Query\Result;
use Blast\Orm\Query\ResultDataDecorator;
use stdClass;

/**
 * Class Mapper
 *
 * Mapping results to entities
 *
 * @package Blast\Db\Orm
 */
class Mapper implements MapperInterface, EntityAwareInterface
{

    use EntityAwareTrait;

    /**
     * Disable direct access to mapper
     * @param array|\ArrayObject|stdClass|DataObject|object|string $entity
     */
    public function __construct($entity)
    {
        if (method_exists($entity, 'attachRelations')) {
            $entity->attachRelations($this);
        }
        $this->setEntity($entity);
    }

    /**
     * Create a new Query instance
     * @return Query
     */
    public function createQuery()
    {
        return new Query($this->getEntity(), Manager::getInstance()->getConnection()->createQueryBuilder());
    }

    /**
     * Find result by field or primary key
     *
     * @param mixed $value
     * @return ArrayObject|stdClass|Result
     */
    public function find($value)
    {
        $query = $this->select();
        $field = MapperHelper::findOption('primaryKeyName', $this->getEntity(), 'id');
        $query->where($query->expr()->eq($field, $query->createPositionalParameter($value)));
        return $query->execute(ResultDataDecorator::RESULT_ENTITY);
    }

    /**
     * Get a collection of all entities
     *
     * @return \ArrayObject|\stdClass|DataObject|object
     */
    public function all()
    {
        return $this->select()->execute(ResultDataDecorator::RESULT_COLLECTION);
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
        $table = MapperHelper::findOption('table', $this->getEntity());
        $query->from($table);

        return $query;
    }

    /**
     * Create a new Model or a collection of entities in storage.
     *
     * @param DataObject|ArrayObject|stdClass|Result|object $entity
     * @return int|int[]|bool[]|bool
     */
    public function create($entity)
    {
        //prepare statement
        $query = $this->createQuery();
        $query->insert(MapperHelper::findOption('table', $entity));
        $data = $entity instanceof DataObjectInterface ? $entity->getData() : DataHelper::receiveDataFromObject($entity);

        //cancel if $data has no entries
        if(count($data) < 1){
            return 0;
        }

        foreach ($data as $key => $value) {
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
     * @param DataObject|ArrayObject|stdClass|Result|object $entity
     * @return int|int[]|bool[]|bool
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function update($entity)
    {
        //prepare statement
        $pkName = MapperHelper::findOption('primaryKeyName', $this->getEntity(), 'id');
        $query = $this->createQuery();
        $query->update(MapperHelper::findOption('table', $entity));

        $data = $entity instanceof UpdatedDataObjectInterface ? $entity->getUpdatedData() : DataHelper::receiveDataFromObject($entity);

        //cancel if $data has no entries
        if(count($data) < 1){
            return 0;
        }

        foreach ($data as $key => $value) {
            $query->set($key, $query->createPositionalParameter($value));
        }

        $query->where($query->expr()->eq($pkName, $data[$pkName]));

        return $query->execute();
    }

    /**
     * Delete attached entity by identifiers
     *
     * @param array|int|string $identifiers
     * @return int
     */
    public function delete($identifiers)
    {
        if (!is_array($identifiers)) {
            $identifiers = [$identifiers];
        }

        $entity = $this->getEntity();

        //prepare statement
        $pkName = MapperHelper::findOption('primaryKeyName', $this->getEntity(), 'id');
        $query = $this->createQuery();
        $query->delete(MapperHelper::findOption('table', $entity));

        //add entities by pk to delete
        foreach ($identifiers as $identifier) {
            $query->orWhere($query->expr()->eq($pkName, $query->createPositionalParameter($identifier)));
        }

        return $query->execute();
    }

    /**
     * Create or update an entity
     *
     * Optional force update of entities without updates
     *
     * @param DataObject|\ArrayObject|\stdClass|Result|object $entity
     * @return int
     */
    public function save($entity)
    {
        return $this->isNewEntity($entity) ? $this->create($entity) : $this->update($entity);
    }

    /**
     * @param DataObject|\ArrayObject|\stdClass|Result|object $entity
     * @return bool
     */
    public function isNewEntity($entity)
    {
        if (method_exists($entity, 'isNew')) {
            return $entity->isNew();
        } elseif (property_exists($entity, 'new')) {
            return $entity->new;
        }

        $data = DataHelper::receiveDataFromObject($entity);
        $pk = MapperHelper::findOption('primaryKeyName', $entity);
        $isNew = true;

        if (!isset($data[$pk])) {
            $isNew = empty($data[$pk]);
        }

        return $isNew;
    }
}