<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 25.11.2015
 * Time: 14:37
 */

namespace Blast\Orm;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Interop\Container\ContainerInterface;

class Manager implements ManagerInterface, ConnectionCollectionInterface
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
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var $this
     */
    protected static $instance;

    /**
     * @var bool
     */
    protected static $booted = false;

    /**
     * Create a new orm capsule
     * @param ContainerInterface $container
     * @param array $connection
     * @return $this
     */
    public static function create(ContainerInterface $container, $connection)
    {
        if (static::isBooted()) {
            throw new \RuntimeException(__CLASS__ . ' is already enabled');
        }

        static::$instance = new self($container, $connection);

        static::$booted = true;

        return static::$instance;
    }

    /**
     * @return $this
     */
    public static function getInstance()
    {
        if (!static::isBooted()) {
            throw new \RuntimeException(__CLASS__ . ' is disabled. Run ' . __CLASS__ . '::create to enable!');
        }

        return static::$instance;
    }

    /**
     * @return boolean
     */
    public static function isBooted()
    {
        return self::$booted;
    }

    /**
     * Close all non persistent connections, deactivate instance and deactivate booted. You need to create a new
     */
    public static function shutdown()
    {
        $instance = static::$instance;
        if($instance instanceof ConnectionCollectionInterface){
            $connections = $instance->getConnections();

            foreach ($connections as $connection) {
                if (!($connection instanceof Connection)) {
                    continue;
                }

                //@todo persistent connections should not disconnected while destruct
                if ($connection->isConnected()) {
                    $connection->close();
                }
            }
        }

        self::$instance = null;
        self::$booted = false;

        return true;
    }

    /**
     * Factory constructor.
     *
     * @param ContainerInterface $container
     * @param array $connection
     */
    private function __construct(ContainerInterface $container, $connection)
    {
        $this->container = $container;
        $this->setContainer($container);
        $this->addConnection(ConnectionCollectionInterface::DEFAULT_CONNECTION, $connection);
    }

    /**
     * disconnect all connections
     */
    public function __destruct(){
        static::shutdown();
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param mixed $container
     * @return $this
     */
    public function setContainer($container)
    {
        $this->container = $container;
        return $this;
    }

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
            $config = new Configuration();
            $connection = DriverManager::getConnection($connection, $config);
        }

        if (!($connection instanceof Connection)) {
            throw new \RuntimeException(sprintf('Connection needs to be an instance of %s', Connection::class));
        }

        return $connection;
    }
}