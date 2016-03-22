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
use Blast\Orm\Entity\ProviderFactoryInterface;
use Blast\Orm\Entity\ProviderFactoryTrait;
use Blast\Orm\Entity\ProviderInterface;
use Blast\Orm\Hydrator\HydratorInterface;
use Blast\Orm\Query;
use Blast\Orm\Relations\BelongsTo;
use Blast\Orm\Relations\HasMany;
use Blast\Orm\Relations\HasOne;
use Blast\Orm\Relations\ManyToMany;
use Blast\Orm\Relations\RelationInterface;
use Doctrine\DBAL\Types\Type;
use stdClass;

/**
 * Each entity does have it's own mapper. A mapper is determined by the entity provider. Mappers mediate between dbal
 * and entity and provide convenient CRUD (Create, Read, Update, Delete). In addition to CRUD, the mapper is also delivering
 * convenient methods to to work with relations.
 *
 * @package Blast\Orm
 */
class Mapper implements EntityAwareInterface, ConnectionAwareInterface, MapperInterface, ProviderFactoryInterface
{

    use ConnectionAwareTrait;
    use EntityAwareTrait;
    use ProviderFactoryTrait;

    /**
     * @var ProviderInterface
     */
    private $provider;

    /**
     * Disable direct access to mapper
     *
     * @param array|\ArrayObject|stdClass|\ArrayObject|object|string $entity
     * @param \Doctrine\DBAL\Driver\Connection $connection
     */
    public function __construct($entity, $connection = null)
    {
        $this->connection = $connection;
        if ($entity instanceof ProviderInterface) {
            $this->setEntity($entity->getEntity());
            $this->provider = $entity;
        } else {
            $this->setEntity($entity);
        }
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
        return new Query($this->getConnection(), $this->getEntity());
    }

    /**
     * @return ProviderInterface
     */
    public function getProvider()
    {
        if (null === $this->provider) {
            $this->provider = $this->createProvider($this->getEntity());
        }

        return $this->provider;
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
        $this->checkEntity($provider);

        //prepare statement
        $query = $this->createQuery();
        $query->insert($provider->getTableName());

        //pass data without relations
        $data = $provider->fetchData();

        //cancel if $data has no entries
        if (count($data) < 1) {
            return false;
        }

        $fields = $provider->getFields();

        foreach ($data as $key => $value) {
            if ($value instanceof RelationInterface) {
                continue;
            };

            $query->setValue($key, $query->createPositionalParameter(
                $value, array_key_exists($key, $fields) ?
                $fields[$key]->getType()->getName() :
                Type::STRING));
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
        $this->checkEntity($provider);

        $pkName = $provider->getPrimaryKeyName();

        //prepare statement
        $query = $this->createQuery();
        $query->update($provider->getTableName());

        //pass data without relations
        $data = $provider->fetchData();

        $fields = $provider->getFields();

        foreach ($data as $key => $value) {
            if ($value instanceof RelationInterface) {
                continue;
            }
            $query->set($key, $query->createPositionalParameter(
                $value, array_key_exists($key, $fields) ?
                $fields[$key]->getType()->getName() :
                Type::STRING));
        }

        $query->where($query->expr()->eq($pkName, $data[$pkName]));

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
        return $this->createProvider($entity)->isNew() ? $this->create($entity) : $this->update($entity);
    }

    /**
     * Prepare delete query for attached entity by identifiers
     *
     * @param array|int|string $identifiers
     * @return query
     */
    public function delete($identifiers)
    {
        $identifiers = is_array($identifiers) ? $identifiers : [$identifiers];

        $provider = $this->getProvider();

        //prepare statement
        $pkName = $provider->getPrimaryKeyName();
        $query = $this->createQuery();
        $query->delete($provider->getTableName());

        //add entities by pk to delete
        foreach ($identifiers as $identifier) {
            if (is_object($identifier)) {
                $identifierProvider = $this->createProvider($identifier);
                $this->checkEntity($identifierProvider);
                $data = $identifierProvider->fetchData();
                $identifier = $data[$pkName];
            }
            $query->orWhere($query->expr()->eq($pkName, $query->createPositionalParameter($identifier)));
        }

        return $query;
    }

    /**
     * BelongsTo is the inverse of a HasOne or a HasMany relation.
     *
     * One entity is associated with one related entity by a field which
     * associates with primary key in related entity.
     *
     * @param $entity
     * @param $foreignEntity
     * @param null $localKey
     *
     * @return Query
     */
    public function belongsTo($entity, $foreignEntity, $localKey = null)
    {
        return $this->prepareRelation(new BelongsTo($entity, $foreignEntity, $localKey));
    }

    /**
     * One entity is associated with one related entity by a field which
     * associates with primary key in current entity.
     *
     * @param $entity
     * @param $foreignEntity
     * @param null|string $foreignKey
     *
     * @return Query
     */
    public function hasOne($entity, $foreignEntity, $foreignKey = null)
    {
        return $this->prepareRelation(new HasOne($entity, $foreignEntity, $foreignKey));
    }

    /**
     * One entity is associated with many related entities
     * by a field which associates with primary key in current entity.
     *
     * @param $entity
     * @param $foreignEntity
     * @param null $foreignKey
     *
     * @return Query
     */
    public function hasMany($entity, $foreignEntity, $foreignKey = null)
    {
        return $this->prepareRelation(new HasMany($entity, $foreignEntity, $foreignKey));
    }

    /**
     * Many entities of type _A_ are associated with many
     * related entities of type _B_ by a junction table.
     * The junction table stores associations from entities
     * of type _A_ to entities of type _B_.
     *
     * @param $entity
     * @param $foreignEntity
     * @param null $foreignKey
     * @param null $localKey
     * @param null $junction
     * @param null $junctionLocalKey
     * @param null $junctionForeignKey
     *
     * @return Query
     */
    public function manyToMany(
        $entity,
        $foreignEntity,
        $foreignKey = null,
        $localKey = null,
        $junction = null,
        $junctionLocalKey = null,
        $junctionForeignKey = null
    ) {

        return $this->prepareRelation(
            new ManyToMany($entity, $foreignEntity, $foreignKey,
                $localKey, $junction, $junctionLocalKey, $junctionForeignKey)
        );

    }

    /**
     * Prepare provider by determining entity type
     *
     * @param $entity
     * @return Provider
     */
    private function prepareProvider($entity)
    {
        if (is_array($entity)) {
            $provider = $this->createProvider($this->getEntity());

            // reset entity in provider and
            // set data
            $provider->setEntity($provider->withData($entity, HydratorInterface::HYDRATE_ENTITY));
        } else {
            $provider = $this->createProvider($entity);
        }

        return $provider;
    }

    /**
     * Share mapper connection with relation
     *
     * @param $relation
     * @return mixed
     */
    private function prepareRelation(RelationInterface $relation)
    {
        if ($relation instanceof ConnectionAwareInterface) {
            $relation->setConnection($this->getConnection());
        }

        return $relation;
    }

    /**
     * Check if external entity matches mapper entity
     *
     * @param $provider
     *
     * @throws \InvalidArgumentException
     */
    private function checkEntity(ProviderInterface $provider)
    {
        $class = get_class($provider->getEntity());
        if ($class !== get_class($this->getProvider()->getEntity())) {
            throw new \InvalidArgumentException('Disallowed usage of differing entity!' . $class);
        }
    }
}
