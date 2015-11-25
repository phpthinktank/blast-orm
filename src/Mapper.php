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

class Mapper
{

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection = null;

    /**
     * @var Factory
     */
    private $factory = null;

    public function __construct($entity, $connectionName = Factory::DEFAULT_CONNECTION)
    {
        $this->factory = Factory::getInstance();
        $this->connection = $this->factory->getConfig()->getConnection($connectionName);


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

}