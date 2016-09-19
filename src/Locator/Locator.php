<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 19.09.2016
 * Time: 13:18
 */

namespace Blast\Orm\Locator;


use Blast\Orm\Connection;
use Blast\Orm\Connection\ConnectionFactory;
use Blast\Orm\ConnectionManager;
use Blast\Orm\ConnectionManagerInterface;
use Blast\Orm\Query;
use Blast\Orm\Query\QueryFactory;
use Blast\Orm\QueryInterface;
use Interop\Container\ContainerInterface;

class Locator implements LocatorInterface
{

    /**
     * @var ClassFactory
     */
    private $classFactory;

    /**
     * List of ORM-Default services
     *
     * @return string[]
     */
    protected $services = [
        QueryInterface::class => Query::class
    ];

    /**
     * @var ContainerInterface[]
     */
    protected static $delegates = [];

    /**
     * @var object|string[]
     */
    protected static $singletons = [
        ConnectionManagerInterface::class, ConnectionManager::class
    ];

    /**
     * @var FactoryInterface[]
     */
    protected static $factories = [
        Query::class => QueryFactory::class,
        Connection::class => ConnectionFactory::class,
    ];

    /**
     * Locator constructor.
     */
    public function __construct(){
        $this->classFactory = new ClassFactory();
    }

    /**
     * @param ContainerInterface|null $delegate
     * @return $this
     */
    public function delegate(ContainerInterface $delegate = null)
    {
        static::$delegates[] = $delegate;
        return $this;
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @param array $arguments
     * @return mixed No entry was found for this identifier.
     * @throws \Exception
     */
    public function get($id, array $arguments = [])
    {
        // try to find service in delegate first
        if ($position = $this->hasInDelegate($id, true)) {
            if (false !== $position) {
                return call_user_func_array([static::$delegates[$position], 'get'], [$id, $arguments]);
            }
        }

        if ($this->hasService($id)) {
            return $this->createInstance($this->services[$id], $arguments);
        }

        if ($this->hasSingleton($id)) {
            $singleton = static::$singletons[$id];
            if (!is_object($singleton)) {
                static::$singletons[$id] = $this->createInstance(static::$singletons[$id], []);
            }

            return static::$singletons[$id];
        }

        throw LocatorException::notFoundException($id);

    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return boolean
     */
    public function has($id)
    {
        return
            $this->hasService($id) ||
            $this->hasInDelegate($id) ||
            $this->hasSingleton($id);
    }

    /**
     * @param $id
     * @return bool
     */
    public function hasService($id)
    {
        return isset($this->services[$id]);
    }

    /**
     * @param $id
     * @param bool $returnPosition
     * @return bool
     */
    public function hasInDelegate($id, $returnPosition = false)
    {
        foreach (static::$delegates as $pos => $delegate) {
            if ($delegate->has($id)) {
                return true === $returnPosition ? $pos : true;
            }
        }
        return false;
    }

    /**
     * @param $id
     * @return bool
     */
    public function hasSingleton($id)
    {
        return isset(static::$singletons[$id]);
    }

    /**
     * @param $class
     * @param array $arguments
     * @return mixed
     */
    private function createInstance($class, array $arguments = [])
    {
        // should not create a new object from class
        if(is_object($class)){
            return $class;
        }

        $factory = $this->classFactory;

        if($this->hasFactory($class)){
            $factory = static::$factories[$class];

            if(!is_object($factory)){
                $factory = $this->classFactory->create($factory, $this, $arguments);
            }

            static::$factories[$class] = $factory;
        }

        return $factory->create($class, $this, $arguments);
    }

    /**
     * @param $class
     * @return bool
     */
    public function hasFactory($class)
    {
        if(isset(static::$factories[$class])){
            $factory = static::$factories[$class];
            return $factory instanceof FactoryInterface;
        }

        return false;
    }

}
