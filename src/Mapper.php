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

class Mapper implements MapperInterface
{

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection = null;

    /**
     * @var Factory
     */
    private $factory = null;

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

    protected function determineDialect(){
        $driverName = $this->getConnection()->getDriver()->getName();
        $dialects = [
            'mysql',
            'pgsql',
            'sqlite',
            'sqlsrv',
        ];

        $dialect = 'common';

        foreach($dialects as $value){
            if(strpos($driverName, $value) !== false){
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
     * @return QueryFactory
     */
    public function getQueryBuilder()
    {
        return new QueryFactory($this->determineDialect());
    }

    public function find($pk){

    }

    public function findBy($field, $pk){
        $query = $this->getQueryBuilder();
        $statement = $query->newSelect();
        $statement->cols(['*']);
        $statement->from($this->getEntity()->getTable());
        $statement->where(':field = :value');
        $statement->bindValue(':field', $field);
        $statement->bindValue(':value', $pk);

        return $this->fetch($statement);
    }

    /**
     * @param $statement
     * @return array
     */
    public function fetch(SelectInterface $statement)
    {
        return $this->getConnection()->fetchAll($statement->getStatement(), $statement->getBindValues());
    }

    public function create($data){
        return $this->getConnection()->insert($this->getEntity(), $data);
    }

    public function update($data, $identifiers){
        return $this->getConnection()->update($this->getEntity(), $data, $identifiers);
    }

    public function delete($identifiers){
        return $this->getConnection()->insert($this->getEntity(), $identifiers);
    }


}