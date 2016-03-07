<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 03.03.2016
 * Time: 10:05
 *
 */

namespace Blast\Orm\Entity;


use Blast\Orm\Data\DataAdapter;
use Blast\Orm\Facades\FacadeFactory;
use Blast\Orm\Object\ObjectAdapterCollection;
use Blast\Orm\Query\Result;
use Interop\Container\ContainerInterface;

class EntityAdapterManager extends ObjectAdapterCollection
{

    /**
     * @var EntityAdapterInterface[]
     */
    private $adapters = [];

    /**
     * @var ContainerInterface
     */
    private $container = null;

    public function __construct()
    {
        $this->container = FacadeFactory::getContainer()->get(ContainerInterface::class);
    }

    /**
     * @param $object
     * @return Result|mixed|object
     */
    public function createObject($object)
    {
        // this is very specific to container
        // @coverageIgnoreStart
        if (is_string($object)) {
            $container = FacadeFactory::getContainer();
            if ($container->has($object)) {
                $object = $container->get($object);
            }
        }
        // @coverageIgnoreEnd

        if (is_string($object)) {
            if (class_exists($object)) {
                $object = new $object;
            } else {
                new GenericEntity($object);
            }
        }

        if(!is_object($object)){
            $object = new Result();
        }

        return $object;
    }

    /**
     * @param null $object
     * @return EntityAdapter
     * @throws \Exception
     */
    public function get($object)
    {
        //@todo should be get by container
            $adapterClassName = EntityAdapter::class;
        $object = $this->createObject($object);
        $hash = $this->getObjectHash($object);
        $container = $this->container;

        if (!isset($adapters[$hash])) {
            if(!is_subclass_of($adapterClassName, DataAdapter::class)){
                throw new \InvalidArgumentException($adapterClassName . ' needs to be an instance of ' . DataAdapter::class);
            }
            $container->add($hash, new $adapterClassName($object), true);
        }

        if(!$container->has($hash)){
            throw new \Exception('Unable to load object from adapter');
        }

        $adapter = $container->get($hash);
        $adapter->setObject($object);

        gc_collect_cycles();

        return $adapter;

    }

    /**
     * @return EntityAdapterInterface[]
     */
    public function getAdapters()
    {
        return $this->adapters;
    }

    /**
     * @param $object
     * @return string
     */
    public function getObjectHash($object)
    {
        if ($object instanceof \ArrayObject) {
            $hash = md5(json_encode(array_keys($object->getArrayCopy())));
            return $hash;
        } elseif ($object instanceof \stdClass) {
            $array = (array)$object;
            $hash = md5(json_encode(array_keys($array)));
            return $hash;
        } else {
            $hash = md5(get_class($object));
            return $hash;
        }
    }
}