<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 25.11.2015
 * Time: 14:33
 */

namespace Blast\Db;


use Doctrine\DBAL\Configuration as DbalConfiguration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

trait ConfigurationTrait
{
    /**
     * @var \Doctrine\DBAL\Connection[]
     */
    protected $connections = [];

    protected $previousConnections = [];

    /**
     * @var Connection
     */
    protected $defaultConnection = null;

    /**
     *
     * Params a related to configuration
     *
     * @see http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#getting-a-connection
     *
     * @param $name
     * @param array|\Doctrine\DBAL\Connection|string $connection
     * @return $this
     */
    public function addConnection($name, $connection)
    {
        if ($this->hasConnection($name)) {
            throw new \InvalidArgumentException(sprintf('Connection with name %s already exists!', $name));
        }


        $connection = $this->determineConnection($connection);

        $this->connections[$name] = $connection;

        //set first connection as active connection
        if (count($this->connections) === 1) {
            $this->setDefaultConnection($name);
        }

        return $this;
    }

    /**
     * Activate a connection as default connection
     * @param string $name
     * @return $this
     */
    public function setDefaultConnection($name)
    {
        if ($this->hasConnection($name)) {
            if($this->defaultConnection !== null){
                $this->previousConnections[] = $this->defaultConnection;
            }
            $this->defaultConnection = $this->getConnection($name);

            return $this;
        }

        throw new \InvalidArgumentException('Unable to activate connection ' . $name);
    }

    /**
     * @return array
     */
    public function getPreviousConnections()
    {
        return $this->previousConnections;
    }

    /**
     * @param $name
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection($name = null)
    {
        if($name === null){
            return $this->defaultConnection;
        }
        if ($this->hasConnection($name)) {
            return $this->connections[$name];
        }

        throw new \InvalidArgumentException('Unknown connection ' . $name);
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
     * @return \Doctrine\DBAL\Connection[]
     */
    public function getConnections()
    {
        return $this->connections;
    }

    /**
     * @param $connection
     * @return Connection
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function determineConnection($connection)
    {
        //assume a valid dsn and convert to connection array
        if (is_array($connection) || is_string($connection)) {
            if (is_string($connection)) {
                $connection = [
                    'url' => $connection
                ];
            }

            //create connection
            $config = new DbalConfiguration();
            $connection = DriverManager::getConnection($connection, $config);
        }

        if (!($connection instanceof Connection)) {
            throw new \RuntimeException(sprintf('Connection needs to be an instance of %s', Connection::class));
        }

        return $connection;
    }
}