<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 25.11.2015
 * Time: 17:39
 */

namespace Blast\Orm;

use ArrayObject;
use Blast\Orm\Data\DataObject;
use Blast\Orm\Entity\EntityAdapter;
use Blast\Orm\Entity\EntityAdapterInterface;
use Blast\Orm\Entity\EntityAdapterLoaderTrait;
use Blast\Orm\Entity\EntityAwareInterface;
use Blast\Orm\Entity\EntityAwareTrait;
use Blast\Orm\Query;
use Blast\Orm\Query\Result;
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
    use EntityAdapterLoaderTrait;

    /**
     * @var EntityAdapterInterface
     */
    private $adapter;

    /**
     * Disable direct access to mapper
     * @param array|\ArrayObject|stdClass|DataObject|object|string $entity
     */
    public function __construct($entity)
    {
        $this->setEntity($entity);
        $this->adapter = $this->loadAdapter($this->getEntity());
    }

    /**
     * Create a new Query instance
     * @return Query
     */
    public function createQuery()
    {
        return new Query($this->getEntity(), ConnectionFacade::getConnection()->createQueryBuilder());
    }

    /**
     * @return EntityAdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Select query for finding entity by primary key
     *
     * @param mixed $primaryKey
     * @return Query
     */
    public function find($primaryKey)
    {
        $query = $this->select();
        $field = $this->getAdapter()->getPrimaryKeyName();
        $query->where($query->expr()->eq($field, $query->createPositionalParameter($primaryKey)));
        return $query;
    }

    /**
     * Select query for entity
     *
     * @param array $selects
     * @return Query
     */
    public function select($selects = ['*'])
    {
        $query = $this->createQuery();
        $query->select($selects);
        $query->from($this->getAdapter()->getTableName());

        return $query;
    }

    /**
     * Create query for new entity.
     *
     * @param array|DataObject|\ArrayObject|\stdClass|Result|object $entity
     * @return Query|bool
     */
    public function create($entity)
    {
        //load entity adaption
        $adapter = $this->prepareAdapter($entity);

        //disallow differing entities
        if($adapter->getClassName() !== $this->getAdapter()->getClassName()){
            throw new \InvalidArgumentException('Try to create differing entity!');
        }

        //prepare statement
        $query = $this->createQuery();
        $query->insert($adapter->getTableName());

        //pass data without relations
        $data = $adapter->getDataWithoutRelations();

        //cancel if $data has no entries
        if(count($data) < 1){
            return false;
        }

        foreach ($data as $key => $value) {
            $query->setValue($key, $query->createPositionalParameter($value));
        }

        return $query;
    }

    /**
     * Update query for existing Model or a collection of entities in storage
     *
     * @param array|DataObject|\ArrayObject|\stdClass|Result|object $entity
     * @return Query
     */
    public function update($entity)
    {
        //load entity adaption
        $adapter = $this->prepareAdapter($entity);

        //disallow differing entities
        if($adapter->getClassName() !== $this->getAdapter()->getClassName()){
            throw new \InvalidArgumentException('Try to update differing entity!');
        }

        $pkName = $adapter->getPrimaryKeyName();

        //prepare statement
        $query = $this->createQuery();
        $query->update($adapter->getTableName());

        //pass data without relations
        $data = $adapter->getDataWithoutRelations();

        foreach ($data as $key => $value) {
            $query->set($key, $query->createPositionalParameter($value));
        }

        $query->where($query->expr()->eq($pkName, $data[$pkName]));

        return $query;
    }

    /**
     * Prepare delete query for attached entity by identifiers
     *
     * @param array|int|string $identifiers
     * @return query
     */
    public function delete($identifiers)
    {
        if (!is_array($identifiers)) {
            $identifiers = [$identifiers];
        }

        $adapter = $this->getAdapter();

        //prepare statement
        $pkName = $adapter->getPrimaryKeyName();
        $query = $this->createQuery();
        $query->delete($adapter->getTableName());

        //add entities by pk to delete
        foreach ($identifiers as $identifier) {
            $query->orWhere($query->expr()->eq($pkName, $query->createPositionalParameter($identifier)));
        }

        return $query;
    }

    /**
     * Create or update an entity
     *
     * @param DataObject|\ArrayObject|\stdClass|Result|object $entity
     * @return Query
     */
    public function save($entity)
    {
        return $this->loadAdapter($entity)->isNew() ? $this->create($entity) : $this->update($entity);
    }

    /**
     * @param $entity
     * @return EntityAdapter $adapter
     */
    private function prepareAdapter($entity)
    {
        if (is_array($entity)) {
            $data = $entity;
            $entity = $this->getEntity();
            $adapter = $this->loadAdapter($entity);
            $adapter->setData($data);
        } elseif (is_object($entity)) {
            $adapter = $this->loadAdapter($entity);
        } else {
            throw new \InvalidArgumentException('entity needs to be an array of data or object. ' . gettype($entity) . ' given');
        }

        return $adapter;
    }
}