<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 25.11.2015
 * Time: 14:37
 */

namespace Blast\Orm;


use Interop\Container\ContainerInterface;

class Factory implements FactoryInterface
{
    const DEFAULT_CONNECTION = 'default';
    /**
     * @var
     */
    protected $config;

    protected $container;

    protected static $instance;
    protected static $booted = false;

    /**
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

        return static::$instance;
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
        $this->setConfig($container->get(ConfigInterface::class));
        $this->getConfig()->addConnection(self::DEFAULT_CONNECTION, $connection);
    }

    /**
     * @return ConfigInterface
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param ConfigInterface $config
     * @return mixed
     */
    public function setConfig(ConfigInterface $config)
    {

        if (!($config instanceof ConfigInterface)) {
            throw new \RuntimeException(sprintf('config needs to be an instance of %s', ConfigInterface::class));
        }
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
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }
}