<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 25.11.2015
 * Time: 14:37
 */

namespace Blast\Db;

use Doctrine\DBAL\Connection;
use Interop\Container\ContainerInterface;

class Factory implements FactoryInterface
{
    /**
     * @var
     */
    protected $config;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var
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
            throw new \RuntimeException('Orm already created!');
        }

        static::$instance = new self($container, $connection);

        static::$booted = true;

        return static::$instance;
    }

    /**
     * disconnect all connections
     */
    public function __destruct(){

        $this->shutdown();

    }

    /**
     * @return $this
     */
    public static function getInstance()
    {
        if (!static::isBooted()) {
            throw new \RuntimeException('Orm not created!');
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
     * Factory constructor.
     *
     * @param ContainerInterface $container
     * @param array $connection
     */
    private function __construct(ContainerInterface $container, $connection)
    {
        $this->container = $container;
        $this->setContainer($container);

        $this->getConfig()->addConnection(ConfigInterface::DEFAULT_CONNECTION, $connection);
    }

    /**
     * @return ConfigInterface
     */
    public function getConfig()
    {
        $config = $this->config;
        if(!($config instanceof ConfigInterface)){
            $container = $this->container;
            if($container->has(ConfigInterface::class)){
                $reflection = new \ReflectionClass($container->get(ConfigInterface::class));
                $config = $reflection->newInstance();
            }else{
                $config = new Config();
            }

            $this->setConfig($config);
        }

        return $this->config;
    }

    /**
     * @param ConfigInterface $config
     * @return mixed
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
        return $this;
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
     * Close all non persistent connections
     */
    public function shutdown()
    {
        $connections = $this->getConfig()->getConnections();

        foreach ($connections as $connection) {
            if (!($connection instanceof Connection)) {
                continue;
            }

            //@todo persistent connections should not disconnected while destruct
            if ($connection->isConnected()) {
                $connection->close();
            }
        }

        static::$instance = null;
        static::$booted = false;
    }
}