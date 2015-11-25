<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 25.11.2015
 * Time: 14:33
 */

namespace Blast\Orm;


use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

class Config implements ConfigInterface
{

    /**
     * @var \Doctrine\DBAL\Connection[]
     */
    protected $connections = [];

    protected $factory = null;

    /**
     * Config constructor.
     * @param Factory|FactoryInterface $factory
     */
    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     *
     * Params a related to configuration
     *
     * @see http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#getting-a-connection
     *
     * @param $name
     * @param array|\Doctrine\DBAL\Connection $connection
     * @return $this
     * @internal param array $params
     */
    public function addConnection($name, $connection)
    {
        if ($this->hasConnection($name)) {
            throw new \InvalidArgumentException(sprintf('Connection with name %s already exists!'));
        }


        $connection = $this->determineConnection($connection);

        $this->connections[$name] = $connection;

        return $this;
    }

    /**
     * @param $name
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection($name)
    {
        return $this->connections[$name];
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasConnection($name)
    {
        return isset($this->connections[$name]);
    }

    /**
     * @param $connection
     * @return Connection
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function determineConnection($connection)
    {
        //if connection is already a connection object use it
        if (is_string($connection) && class_exists($connection)) {
            $connection = $this->factory->getContainer()->get($connection);
        } else {
            //assume a valid dsn and convert to connection array
            if (is_string($connection) && !class_exists($connection)) {
                $connection = [
                    'url' => $connection
                ];
            }

            //create connection
            $config = new Configuration();
            $connection = DriverManager::getConnection($connection, $config);
        }

        if (!($connection instanceof Connection)) {
            throw new \RuntimeException(sprintf('Connection needs to be an instance of %s', Connection::class));
        }

        return $connection;
    }
}