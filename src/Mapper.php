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
use Blast\Orm\Hydrator\HydratorInterface;
use Blast\Orm\Query;
use Blast\Orm\Relations\RelationInterface;
use stdClass;

/**
 * Class Mapper
 *
 * Mapping results to entities
 *
 * @package Blast\Db\Orm
 */
class Mapper implements MapperInterface, EntityAwareInterface, LocatorAwareInterface
{

    use EntityAwareTrait;
    use LocatorAwareTrait;

    /**
     * @var ProviderInterface
     */
    private $provider;

    /**
     * @var \Doctrine\DBAL\Driver\Connection|null
     */
    private $connection;

    /**
     * Disable direct access to mapper
     * @param LocatorInterface $locator
     * @param array|\ArrayObject|stdClass|\ArrayObject|object|string $entity
     * @param \Doctrine\DBAL\Driver\Connection $connection
     */
    public function __construct(LocatorInterface $locator, $entity, $connection = null)
    {
        $this->connection = $connection;
        $this->locator = $locator;
        if ($entity instanceof ProviderInterface) {
            $this->setEntity($entity->getEntity());
            $this->provider = $entity;
        } else {
            $this->setEntity($entity);
            $this->provider = $this->getLocator()->getProvider($this->getEntity());
        }
    }

    /**
     * Get current connection
     *
     * @return \Doctrine\DBAL\Driver\Connection|\Doctrine\DBAL\Connection
     */
    public function getConnection()
    {
        if(null === $this->connection){
            $this->connection = $this->getLocator()->getConnectionManager()->get();
        }
        return $this->connection;
    }

    /**
     * @param \Doctrine\DBAL\Driver\Connection|null $connection
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
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
     * Create a new Query instance
     * @return Query
     */
    public function createQuery()
    {
        $query = new Query($this->getLocator(), $this->getConnection(), $this->getEntity());
        return $query;
    }

    /**
     * @return ProviderInterface
     */
    public function getProvider()
    {
        return $this->provider;
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

        $provider = $this->getProvider();

        //prepare statement
        $pkName = $provider->getPrimaryKeyName();
        $query = $this->createQuery();
        $query->delete($provider->getTableName());

        //add entities by pk to delete
        foreach ($identifiers as $identifier) {
            $query->orWhere($query->expr()->eq($pkName, $query->createPositionalParameter($identifier)));
        }

        return $query;
    }

    /**
     * Create or update an entity
     *
     * @param \ArrayObject|\SplStack|\stdClass|object $entity
     * @return Query
     */
    public function save($entity)
    {
        return $this->getLocator()->getProvider($entity)->isNew() ? $this->create($entity) : $this->update($entity);
    }

    /**
     * Create query for new entity.
     *
     * @param array|\ArrayObject|\stdClass|object $entity
     * @return Query|bool
     */
    public function create($entity)
    {
        //load entity provider
        $provider = $this->prepareProvider($entity);

        //disallow differing entities
        if (get_class($provider->getEntity()) !== get_class($this->getProvider()->getEntity())) {
            throw new \InvalidArgumentException('Try to create differing entity!');
        }

        //prepare statement
        $query = $this->createQuery();
        $query->insert($provider->getTableName());

        //pass data without relations
        $data = $provider->fromObjectToArray();

        //cancel if $data has no entries
        if (count($data) < 1) {
            return false;
        }

        foreach ($data as $key => $value) {
            if ($value instanceof RelationInterface) {
                continue;
            }
            $query->setValue($key, $query->createPositionalParameter($value));
        }

        return $query;
    }

    /**
     * Update query for existing Model or a collection of entities in storage
     *
     * @param array|\ArrayObject|\stdClass|object $entity
     * @return Query
     */
    public function update($entity)
    {
        //load entity provider
        $provider = $this->prepareProvider($entity);

        //disallow differing entities
        if (get_class($provider->getEntity()) !== get_class($this->getProvider()->getEntity())) {
            throw new \InvalidArgumentException('Try to update differing entity!');
        }

        $pkName = $provider->getPrimaryKeyName();

        //prepare statement
        $query = $this->createQuery();
        $query->update($provider->getTableName());

        //pass data without relations
        $data = $provider->fromObjectToArray();

        foreach ($data as $key => $value) {
            if ($value instanceof RelationInterface) {
                continue;
            }
            $query->set($key, $query->createPositionalParameter($value));
        }

        $query->where($query->expr()->eq($pkName, $data[$pkName]));

        return $query;
    }

    /**
     * @param $entity
     * @return Provider
     */
    private function prepareProvider($entity)
    {
        if (is_array($entity)) {
            $provider = $this->getLocator()->getProvider($this->getEntity());

            //reset entity in provider
            $provider->setEntity($provider->fromArrayToObject($entity, HydratorInterface::HYDRATE_ENTITY));
        } else {
            $provider = $this->getLocator()->getProvider($entity);
        }
        return $provider;
    }
}
