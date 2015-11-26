<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 25.11.2015
 * Time: 17:39
 */

namespace Blast\Orm;


use Aura\SqlQuery\Common\SelectInterface;
use Aura\SqlQuery\Common\SubselectInterface;
use Aura\SqlQuery\QueryFactory;
use Blast\Orm\Entity\EntityInterface;
use Blast\Orm\Entity\GenericEntity;
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

    public function __construct($entity, $connectionName = Factory::DEFAULT_CONNECTION)
    {
        $this->factory = Factory::getInstance();
        $this->connection = $this->factory->getConfig()->getConnection($connectionName);
        $this->entity = $this->determineEntity($entity);
    }

    protected function determineDialect()
    {
        $driverName = $this->getConnection()->getDriver()->getName();
        $dialects = [
            'mysql',
            'pgsql',
            'sqlite',
            'sqlsrv',
        ];

        $dialect = 'common';

        foreach ($dialects as $value) {
            if (strpos($driverName, $value) !== FALSE) {
                $dialect = $value;
            }
        }

        return ucfirst($dialect);
    }

    protected function determineEntity($entity)
    {
        //if connection is already a connection object use it
        if (is_string($entity) && class_exists($entity)) {
            $entity = $this->factory->getContainer()->get($entity);
        } elseif (is_string($entity) && !class_exists($entity)) {
            //assume a valid dsn and convert to connection array

            $entity = new GenericEntity($entity);
        }


        if (!($entity instanceof EntityInterface)) {
            throw new \RuntimeException(sprintf('Connection needs to be an instance of %s', EntityInterface::class));
        }

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
     * @return array
     */
    public function findBy($field, $value)
    {
        $query = $this->getQueryBuilder();
        $statement = $query->select('*')
            ->from($this->getEntity()->getTable())
            ->where($field . ' = :value');

        $statement->setParameter(':value', $value);

        return $this->fetch($statement);
    }

    /**
     * @param $statement
     * @return array
     */
    public function fetch(QueryBuilder $statement)
    {
        return $this->getConnection()->fetchAll($statement->getSQL(), $statement->getParameters());
    }

    /**
     * @param $data
     * @return int
     */
    public function create($data)
    {
        return $this->getConnection()->insert($this->getEntity(), $data);
    }

    /**
     * @param $data
     * @param $identifiers
     * @return int
     */
    public function update($data, $identifiers)
    {
        return $this->getConnection()->update($this->getEntity(), $data, $identifiers);
    }

    /**
     * @param $identifiers
     * @return int
     */
    public function delete($identifiers)
    {
        return $this->getConnection()->insert($this->getEntity(), $identifiers);
    }


}