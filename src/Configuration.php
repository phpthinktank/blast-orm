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

class Configuration implements ConfigurationInterface
{
    /**
     * @var \Doctrine\DBAL\Connection[]
     */
    protected $connections = [];

    /**
     * @var Connection
     */
    protected $activeConnection = null;

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
            throw new \InvalidArgumentException(sprintf('Connection with name %s already exists!'));
        }


        $connection = $this->determineConnection($connection);

        $this->connections[$name] = $connection;

        //set first connection as active connection
        if (count($this->connections) === 1) {
            $this->setActiveConnection($name);
        }

        return $this;
    }

    /**
     * Activate a connection as default connection
     * @param string $name
     * @return $this
     */
    public function setActiveConnection($name)
    {
        if ($this->hasConnection($name)) {
            $this->activeConnection = $this->getConnection($name);
            return $this;
        }

        throw new \InvalidArgumentException('Unable to activate connection ' . $name);
    }

    /**
     * @param $name
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection($name = self::DEFAULT_CONNECTION)
    {
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