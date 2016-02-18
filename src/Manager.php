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

class Manager implements ManagerInterface, ConfigurationInterface
{

    use ConfigurationTrait;

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
        static::shutdown();
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
     * Factory constructor.
     *
     * @param ContainerInterface $container
     * @param array $connection
     */
    private function __construct(ContainerInterface $container, $connection)
    {
        $this->container = $container;
        $this->setContainer($container);
        $this->addConnection(ConfigurationInterface::DEFAULT_CONNECTION, $connection);
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
     * Close all non persistent connections, deactivate instance and deactivate booted. You need to create a new
     */
    public static function shutdown()
    {
        $instance = static::$instance;
        if($instance instanceof ConfigurationInterface){
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
}