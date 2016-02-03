<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 25.11.2015
 * Time: 14:37
 */

namespace Blast\Db\Orm;

use Blast\Db\Config;
use Blast\Db\ConfigInterface;
use Blast\Db\Entity\EntityInterface;
use Blast\Db\Entity\ManagerInterface;
use Doctrine\DBAL\Connection;
use Interop\Container\ContainerInterface;
use League\Event\EmitterInterface;

class Factory implements FactoryInterface
{
    const DEFAULT_CONNECTION = 'default';
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
        $this->getConfig()->addConnection(self::DEFAULT_CONNECTION, $connection);
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

    /**
     * Create mapper from entity
     *
     * @param $mapper
     * @return MapperInterface
     */
    public function createMapper($mapper = null){

        if($mapper === null){
            $mapper = MapperInterface::class;
        }

        if(is_string($mapper)){
            $mapper = $this->getContainer()->get($mapper);
        }

        if(!($mapper instanceof MapperInterface)){
            throw new \RuntimeException('Mapper needs to be an instance of ' . MapperInterface::class);
        }

        return $mapper;
    }
}