<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 02.03.2016
 * Time: 12:59
 *
 */

namespace Blast\Orm\Object;


use Blast\Orm\Data\DataAdapter;
use Blast\Orm\Facades\FacadeFactory;
use Interop\Container\ContainerInterface;

class ObjectAdapterCollection
{
    /**
     * @var ObjectAdapterInterface[]
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
     * @param null $object
     * @param null $adapterClassName
     * @return DataAdapter
     * @throws \Exception
     */
    public function get($object, $adapterClassName = null)
    {
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
     * @param $object
     * @return object
     */
    public function createObject($object)
    {
        if (is_string($object)) {
            if (class_exists($object)) {
                $object = new $object;
            } else {
                throw new InvalidObjectFromStringException('Unable to create object from string: ' . $object);
            }
        }
        return $object;
    }

    /**
     * @return ObjectAdapterInterface[]
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