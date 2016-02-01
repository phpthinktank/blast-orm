<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 25.11.2015
 * Time: 17:39
 */

namespace Blast\Db\Orm;

use Blast\Db\Entity\EntityInterface;
use Blast\Db\Entity\Manager;
use Blast\Db\Events\ResultEvent;
use Doctrine\DBAL\Query\QueryBuilder;

class Mapper implements MapperInterface
{

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection = NULL;

    /**
     * @var Factory
     */
    private $factory = NULL;

    /**
     * @var EntityInterface
     */
    private $entity;

    /**
     * @var Manager
     */
    private $manager;


    public function __construct($entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection()
    {
        if($this->connection === null){
            $this->connection = $this->factory->getConfig()->getConnection(Factory::DEFAULT_CONNECTION);
        }
        return $this->connection;
    }

    /**
     * @param \Doctrine\DBAL\Connection $connection
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return Factory
     */
    public function getFactory()
    {
        if($this->factory === null){
            $this->factory = Factory::getInstance();
        }
        return $this->factory;
    }

    /**
     * @param Factory $factory
     */
    public function setFactory($factory)
    {
        $this->factory = $factory;
    }

    /**
     * @return EntityInterface
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->getConnection()->createQueryBuilder();
    }

    /**
     * @return Manager
     */
    public function getManager()
    {
        if($this->manager === null){
            $this->manager = new Manager($this->getEntity(), $this, $this->getFactory());
        }
        return $this->manager;
    }

    /**
     * @param Manager $manager
     */
    public function setManager($manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param $pk
     * @return array
     */
    public function find($pk)
    {
        $field = array_shift($this->getEntity()->getTable()->getPrimaryKey()->getColumns());

        return $this->findBy($field, $pk);
    }

    /**
     * @param $field
     * @param $value
     * @return EntityInterface|EntityInterface[]
     */
    public function findBy($field, $value)
    {
        $query = $this->getQueryBuilder();
        $statement = $query->select('*')
            ->from($this->getEntity()->getTable())
            ->where($field . ' = :value')
            ->setParameter(':value', $value, $this->getEntity()->getTable()->getColumn($field)->getType());

        return $this->fetch($statement);
    }

    /**
     * @param QueryBuilder $statement
     * @param bool $raw
     * @return array
     */
    public function fetch(QueryBuilder $statement, $raw = FALSE)
    {
        $result = $this->getConnection()->executeQuery($statement->getSQL(), $statement->getParameters())->fetchAll();

        return $raw === TRUE ? $result : $this->determineResultSet($result);
    }

    /**
     * @param EntityInterface|EntityInterface[] $entity
     * @return int
     * @internal param $data
     */
    public function create($entity)
    {
        if(is_array($entity)){
            return $this->batchOperation(__FUNCTION__, $entity);
        }
        $manager = $this->getManager();
        $entity = $manager->create($entity);

        $event = $entity->getEmitter()->emit($entity::BEFORE_CREATE, $entity);

        if ($event->isPropagationStopped()) {
            return false;
        }

        $result = $this->getConnection()->insert($entity->getTable()->getName(), $entity->getData(), $entity->getTable()->getColumnsTypes());

        $event = $entity->getEmitter()->emit(new ResultEvent($entity::AFTER_CREATE, $result), $entity);
        return $event->isPropagationStopped() ? false : $result;
    }

    /**
     * @param EntityInterface|EntityInterface[] $entity
     * @return int
     */
    public function update($entity)
    {
        if (is_array($entity)) {
            return $this->batchOperation(__FUNCTION__, $entity);
        }
        $manager = $this->getManager();
        $entity = $manager->create($entity);

        $event = $entity->getEmitter()->emit($entity::BEFORE_UPDATE, $entity);

        if ($event->isPropagationStopped()) {
            return false;
        }

        $pkName = array_shift($entity->getTable()->getPrimaryKey()->getColumns());
        $result = $this->getConnection()->update($entity->getTable()->getName(), $entity->getData(), [$pkName = $entity->__get($pkName)]);

        $event = $entity->getEmitter()->emit(new ResultEvent($entity::AFTER_UPDATE, $result), $entity);
        return $event->isPropagationStopped() ? false : $result;
    }

    /**
     * @param EntityInterface|EntityInterface[] $entity
     * @return int
     */
    public function delete($entity)
    {
        if (is_array($entity)) {
            return $this->batchOperation(__FUNCTION__, $entity);
        }

        $manager = $this->getManager();
        $entity = $manager->create($entity);

        $event = $entity->getEmitter()->emit($entity::BEFORE_DELETE, $entity);

        if ($event->isPropagationStopped()) {
            return false;
        }

        $pkName = array_shift($entity->getTable()->getPrimaryKey()->getColumns());
        $result = $this->getConnection()->delete($entity->getTable()->getName(), [$pkName = $entity->__get($pkName)]);

        $event = $entity->getEmitter()->emit(new ResultEvent($entity::AFTER_DELETE, $result), $entity);
        return $event->isPropagationStopped() ? false : $result;
    }

    /**
     * @param EntityInterface|EntityInterface[] $entity
     * @return int
     */
    public function save($entity)
    {
        if (is_array($entity)) {
            return $this->batchOperation(__FUNCTION__, $entity);
        }
        return $entity->isNew() ? $this->create($entity) : $this->update($entity);
    }

    /**
     * Analyse result and return correct set
     * @param $data
     * @return EntityInterface|EntityInterface[]|null
     */
    protected function determineResultSet($data)
    {
        $count = count($data);
        $result = NULL;

        if ($count > 1) { //if result set has many items, return a collection of entities
            $result = [];
            foreach ($data as $item) {
                $result[] = $this->getManager()->create()->setData($item);
            }
        } elseif ($count === 1) { //if result has one item, return the entity
            $result = $this->getManager()->create()->setData(array_shift($data));
        }

        return $result;
    }

    /**
     * Execute batch delete, save, update or insert
     * @param $operation
     * @param $entity
     * @return array
     */
    private function batchOperation($operation, $entity)
    {
        $results = [];
        foreach ($entity as $_) {
            $this->{$operation}($_);
        }
        return $results;
    }

}