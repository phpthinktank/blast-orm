<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 25.11.2015
 * Time: 17:39
 */

namespace Blast\Orm;

use Blast\Orm\Entity\EntityInterface;
use Blast\Orm\Entity\GenericEntity;
use Doctrine\DBAL\Query\QueryBuilder;
use League\Event\Emitter;
use League\Event\EmitterInterface;

class Mapper implements MapperInterface
{

    const EVENT_BEFORE_SAVE = 'mapper.save.before';
    const EVENT_AFTER_SAVE = 'mapper.save.before';
    const EVENT_BEFORE_CREATE = 'mapper.create.before';
    const EVENT_AFTER_CREATE = 'mapper.create.before';
    const EVENT_BEFORE_UPDATE = 'mapper.update.before';
    const EVENT_AFTER_UPDATE = 'mapper.update.before';
    const EVENT_BEFORE_DELETE = 'mapper.delete.before';
    const EVENT_AFTER_DELETE = 'mapper.delete.before';
    const EVENT_BEFORE_GET = 'mapper.get.before';
    const EVENT_AFTER_GET = 'mapper.get.before';

    private $events = [
        self::EVENT_AFTER_CREATE,
        self::EVENT_BEFORE_CREATE,
        self::EVENT_AFTER_DELETE,
        self::EVENT_BEFORE_DELETE,
        self::EVENT_AFTER_GET,
        self::EVENT_BEFORE_GET,
        self::EVENT_AFTER_SAVE,
        self::EVENT_BEFORE_SAVE,
        self::EVENT_AFTER_UPDATE,
        self::EVENT_BEFORE_UPDATE,
    ];

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

    public function __construct($entity, $connectionName = Factory::DEFAULT_CONNECTION)
    {
        $this->factory = Factory::getInstance();
        $this->connection = $this->factory->getConfig()->getConnection($connectionName);
        $this->entity = $this->determineEntity($entity);
    }

    /**
     * @return Emitter
     */
    public function getEmitter()
    {
        return $this->getFactory()->getContainer()->get(EmitterInterface::class);
    }

    protected function determineEntity($entity)
    {

        if (is_string($entity) && class_exists($entity)) {
            $entity = $this->factory->getContainer()->get($entity);
        } elseif (is_string($entity) && !class_exists($entity)) {
            $entity = new GenericEntity($entity);
        }

        if (!($entity instanceof EntityInterface)) {
            throw new \RuntimeException(sprintf('Connection needs to be an instance of %s', EntityInterface::class));
        }

        //set default values
        $fields = $entity->fields();
        foreach ($fields as $name => $field) {
            if (!isset($field['type'])) {
                continue;
            }
            $entity->__set($name, $field['default']);
        }

        //set events
        $emitter = $this->getEmitter();
        $emitter->removeAllListeners($this->events);
        $entity->events($emitter);

        return $entity;
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @return Factory
     */
    public function getFactory()
    {
        return $this->factory;
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
     * @param $pk
     * @return array
     */
    public function find($pk)
    {
        $field = $this->getEntity()->primaryKeyField();
        if ($field === FALSE) {
            throw new \RuntimeException('Entity does not have a primary key field. Please try findBy()');
        }

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
            ->setParameter(':value', $value, $this->getFieldType($field));

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
     * @param $data
     * @return int
     */
    public function create($data)
    {
        return $this->getConnection()->insert($this->getEntity()->getTable(), $data);
    }

    /**
     * @param $data
     * @param $identifiers
     * @return int
     */
    public function update($data, $identifiers)
    {
        return $this->getConnection()->update($this->getEntity()->getTable(), $data, $identifiers);
    }

    /**
     * @param $identifiers
     * @return int
     */
    public function delete($identifiers)
    {
        return $this->getConnection()->delete($this->getEntity()->getTable(), $identifiers);
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

        if ($count > 1) {
            $result = [];
            foreach ($data as $item) {
                $result[] = $this->getEntityInstance()->setData($item);
            }
        } elseif ($count === 1) {
            $result = $this->getEntityInstance()->setData(array_shift($data));
        }

        return $result;
    }

    /**
     * @return EntityInterface
     */
    protected function getEntityInstance()
    {
        return (new \ReflectionObject($this->getEntity()))->newInstance();
    }

    protected function getFieldType($field)
    {
        $fields = $this->getEntity()->fields();

        if (isset($fields[ $field ])) {
            if (isset($fields[ $field ]['type'])) {
                return $fields[ $field ]['type'];
            }
        }

        return NULL;
    }


}