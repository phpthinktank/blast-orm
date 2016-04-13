<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 25.11.2015
 * Time: 17:39
 */

namespace Blast\Orm;

use ArrayObject;
use Blast\Orm\Entity\DefinitionInterface;
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
     * @var DefinitionInterface
     */
    private $definition;

    /**
     * Disable direct access to mapper
     *
     * @param array|\ArrayObject|stdClass|\ArrayObject|object|string $entity
     * @param \Doctrine\DBAL\Driver\Connection $connection
     */
    public function __construct($entity, $connection = null)
    {
        $this->connection = $connection;
        if ( $entity instanceof DefinitionInterface ) {
            $this->setEntity($entity->getEntity());
            $this->definition = $entity;
        } elseif ( $entity instanceof ProviderInterface ) {
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
        $query->where($query->expr()->eq(
            $this->getDefinition()->getPrimaryKeyName(),
            $query->createPositionalParameter($primaryKey))
        );

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
        return $this->createQuery()
            ->select($selects)
            ->from($this->getDefinition()->getTableName());
    }

    /**
     * Create a new Query instance
     * @return \Blast\Orm\Query
     */
    public function createQuery()
    {
        return new Query($this->getConnection(), $this->getEntity());
    }

    /**
     * Create a new Query instance
     * @return \Blast\Orm\Gateway
     */
    public function createGateway($tableName)
    {
        $gateway = new Gateway($tableName);
        $gateway->setConnection($this->getConnection());

        return $gateway;
    }

    /**
     * @return ProviderInterface
     */
    public function getProvider()
    {
        if ( null === $this->provider ) {
            $this->provider = $this->createProvider($this->getEntity());
        }

        return $this->provider;
    }

    /**
     * @return DefinitionInterface
     */
    public function getDefinition()
    {
        if ( null === $this->definition ) {
            $this->definition = $this->createProvider($this->getEntity())->getDefinition();
        }

        return $this->definition;
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
        $definition = $provider->getDefinition();

        //disallow differing entities
        return $this->checkEntity($definition->getEntity())
            ->createGateway($definition->getTableName())
            ->insert($provider->extract(), $definition->getFields());
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
        $definition = $provider->getDefinition();

        //disallow differing entities
        return $this->checkEntity($definition->getEntity())
            ->createGateway($definition->getTableName())
            ->update(
                $definition->getPrimaryKeyName(),
                $provider->extract(),
                $definition->getFields()
            );
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
     * @param int|string $identifier
     * @return query
     */
    public function delete($identifier)
    {
        $definition = $this->getDefinition();

        //prepare statement
        $primaryKeyName = $definition->getPrimaryKeyName();

        if ( is_object($identifier) ) {
            $identifierProvider = $this->createProvider($identifier);
            $this->checkEntity($identifierProvider->getEntity());
            $data = $identifierProvider->extract();
            $identifier = $data[$primaryKeyName];
        }

        return $this
            ->createGateway($definition->getTableName())
            ->delete($primaryKeyName, $identifier);
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
        if ( is_array($entity) ) {
            $provider = $this->createProvider($this->getEntity());

            // reset entity in provider and
            // set data
            $provider->setEntity($provider->hydrate($entity, HydratorInterface::HYDRATE_ENTITY));
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
        if ( $relation instanceof ConnectionAwareInterface ) {
            $relation->setConnection($this->getConnection());
        }

        return $relation;
    }

    /**
     * Check if external entity matches mapper entity
     *
     * @param $entity
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    private function checkEntity($entity)
    {
        if ( get_class($entity) !== get_class($this->getDefinition()->getEntity()) ) {
            throw new \InvalidArgumentException('Disallowed usage of differing entity!' . get_class($entity));
        }

        return $this;
    }
}
