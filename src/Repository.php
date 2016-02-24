<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 25.11.2015
 * Time: 17:39
 */

namespace Blast\Orm;

use ArrayObject;
use Blast\Orm\Data\DataHelper;
use Blast\Orm\Data\DataObject;
use Blast\Orm\Data\DataObjectInterface;
use Blast\Orm\Data\UpdatedDataObjectInterface;
use Blast\Orm\Entity\EntityAdapter;
use Blast\Orm\Entity\EntityAdapterInterface;
use Blast\Orm\Entity\EntityAdapterLoaderTrait;
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
class Repository implements RepositoryInterface, EntityAwareInterface
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
        return new Query($this->getEntity(), Manager::getInstance()->getConnection()->createQueryBuilder());
    }

    /**
     * @return EntityAdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
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
        $field = $this->getAdapter()->getPrimaryKeyName();
        $query->where($query->expr()->eq($field, $query->createPositionalParameter($value)));
        return $query->execute(EntityAdapter::RESULT_ENTITY);
    }

    /**
     * Get a collection of all entities
     *
     * @return \ArrayObject|\stdClass|DataObject|object
     */
    public function all()
    {
        return $this->select()->execute(EntityAdapter::RESULT_COLLECTION);
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
        $query->from($this->getAdapter()->getTableName());

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
        $adapter = $this->loadAdapter($entity);
        $query->insert($adapter->getTableName());
        $data = $adapter->getData();

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

        $adapter = $this->loadAdapter($entity);
        $pkName = $adapter->getPrimaryKeyName();
        $query = $this->createQuery();
        $query->update($adapter->getTableName());

        $data = $adapter->getData();

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
        $adapter = $this->getAdapter();

        //prepare statement
        $pkName = $adapter->getPrimaryKeyName();
        $query = $this->createQuery();
        $query->delete($adapter->getTableName());

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
        return $this->loadAdapter($entity)->isNew() ? $this->create($entity) : $this->update($entity);
    }
}