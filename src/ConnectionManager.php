<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 25.11.2015
 * Time: 14:37
 */

namespace Blast\Orm;

use Blast\Orm\Facades\FacadeFactory;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception\InvalidArgumentException;

class ConnectionManager implements ConnectionManagerInterface
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
     * disconnect all connections and remove all connections
     */
    public function closeAll()
    {
        $connections = $this->all();

        foreach ($connections as $connection) {
            if ($connection->isConnected()) {
                $connection->close();
            }
        }

        gc_collect_cycles();

        $this->connections = [];
    }

    /**
     * Close all connections on
     */
    public function __destruct()
    {
        $this->closeAll();
    }

    /**
     *
     * Params a related to configuration
     *
     * @see http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#getting-a-connection
     *
     * @param array|\Doctrine\DBAL\Connection|string $connection
     * @param string $name
     *
     * @return $this
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function add($connection, $name = self::DEFAULT_CONNECTION)
    {
        if ($this->has($name)) {
            throw new DBALException(sprintf('Connection with name %s already exists!', $name));
        }

        $connection = static::create($connection);

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
     *
     * @return $this
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function setDefaultConnection($name)
    {
        if (!$this->has($name)) {
            throw new DBALException(sprintf('Connection with name %s not found!', $name));
        }

        if ($this->defaultConnection !== null) {
            $this->previousConnections[] = $this->defaultConnection;
        }
        $this->defaultConnection = $this->get($name);

        return $this;
    }

    /**
     * @return array
     */
    public function getPrevious()
    {
        return $this->previousConnections;
    }

    /**
     * Get connection by name.
     *
     * @param $name
     *
     * @return \Doctrine\DBAL\Connection
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function get($name = null)
    {
        if ($name === null) {
            return $this->defaultConnection;
        }
        if ($this->has($name)) {
            return $this->connections[$name];
        }

        throw new DBALException('Unknown connection ' . $name);
    }

    /**
     * Check if connections exists
     *
     * @param $name
     * @return bool
     */
    public function has($name)
    {
        return isset($this->connections[$name]);
    }

    /**
     * Get all connections
     *
     * @return \Doctrine\DBAL\Connection[]
     */
    public function all()
    {
        return $this->connections;
    }

    /**
     * Determine connection from definition.
     *
     * If definition is a string, the manager tries to get definition from ioc container,
     * otherwise the manager assumes a valid dsn string and converts definition to an array.
     *
     * If definition is a string manager is determining wrapper class and tries to get wrapper
     * class from container.
     *
     * @param $definition
     * @return Connection
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function create($definition)
    {
        // create connection from definition
        if($definition instanceof Connection){
            return $definition;
        }

        $container = FacadeFactory::getContainer();
        // assume a valid service from IoC container
        // or assume a valid dsn and convert to connection array
        if (is_string($definition)) {
            $definition = $container->has($definition) ?
                $container->get($definition) :
                ['url' => $definition];
        }

        if (is_array($definition)) {

            // try to get wrapper class from container
            $wrapperClass = \Doctrine\DBAL\Driver\Connection::class;
            if (isset($definition['wrapperClass'])) {
                $wrapperClass = is_string($definition['wrapperClass']) ? $definition['wrapperClass'] : $wrapperClass;
            }

            if ($container->has($wrapperClass)) {
                $definition['wrapperClass'] = $container->get($wrapperClass);
            }
        }

        if(!is_array($definition)){
            throw new DBALException('Unable to determine parameter array from definition');
        }

        $connection = DriverManager::getConnection($definition);

        if (!($connection instanceof Connection)) {
            throw new \RuntimeException(sprintf('Connection needs to be an instance of %s', Connection::class));
        }

        return $connection;
    }
}