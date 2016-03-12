<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 25.11.2015
 * Time: 17:39
 */

namespace Blast\Orm;

use ArrayObject;
use Blast\Orm\Entity\EntityAwareInterface;
use Blast\Orm\Entity\EntityAwareTrait;
use Blast\Orm\Entity\Provider;
use Blast\Orm\Entity\ProviderInterface;
use Blast\Orm\Query;
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
     * @var AdapterInterface
     */
    private $provider;

    /**
     * Disable direct access to mapper
     * @param array|\ArrayObject|stdClass|\ArrayObject|object|string $entity
     */
    public function __construct($entity)
    {
        if($entity instanceof ProviderInterface){
            $this->setEntity($entity->getEntity());
            $this->provider = $entity;
        }else{
            $this->setEntity($entity);
            $this->provider = LocatorFacade::getProvider($this->getEntity());
        }
    }

    /**
     * Create a new Query instance
     * @return Query
     */
    public function createQuery()
    {
        return new Query($this->getEntity(), LocatorFacade::getConnectionManager()->getConnection()->createQueryBuilder());
    }

    /**
     * @return ProviderInterface
     */
    public function getProvider()
    {
        return $this->provider;
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
        $field = $this->getProvider()->getPrimaryKeyName();
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
        $query->from($this->getProvider()->getTableName());

        return $query;
    }

    /**
     * Create query for new entity.
     *
     * @param array|\ArrayObject|\ArrayObject|\stdClass|Entity|object $entity
     * @return Query|bool
     */
    public function create($entity)
    {
        //load entity adaption
        $adapter = $this->prepareProvider($entity);

        //disallow differing entities
        if(get_class($adapter->getEntity()) !== $this->getProvider()->getClassName()){
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
     * @param array|\ArrayObject|\ArrayObject|\stdClass|Entity|object $entity
     * @return Query
     */
    public function update($entity)
    {
        //load entity adaption
        $adapter = $this->prepareProvider($entity);

        //disallow differing entities
        if($adapter->getClassName() !== $this->getProvider()->getClassName()){
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

        $adapter = $this->getProvider();

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
     * @param \ArrayObject|\ArrayObject|\stdClass|Entity|object $entity
     * @return Query
     */
    public function save($entity)
    {
//        return LocatorFacade::getProvider($entity)->isNew() ? $this->create($entity) : $this->update($entity);
    }

    /**
     * @param $entity
     * @return Provider $adapter
     */
    private function prepareProvider($entity)
    {
        if (is_array($entity)) {
            $data = $entity;
            $entity = $this->getEntity();
            $adapter = LocatorFacade::getProvider($entity);
//            $adapter->setData($data);
        } elseif (is_object($entity)) {
            $adapter = LocatorFacade::getProvider($entity);
        } else {
            throw new \InvalidArgumentException('entity needs to be an array of data or object. ' . gettype($entity) . ' given');
        }

        return $adapter;
    }
}