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

use Blast\Orm\Facades\FacadeFactory;
use Interop\Container\ContainerInterface;

class EntityAdapterManager
{

    /**
     * @var AdapterInterface[]
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
     * @param object|string $object
     * @return EntityAdapter
     * @throws \Exception
     */
    public function get($object)
    {
        $object = $this->createObject($object);
        $hash = $this->getObjectHash($object);
        $container = $this->container;

        if (!$container->has($hash)) {
            $container->add($hash, new EntityAdapter($object), true);
        }

        $adapter = $container->get($hash);
        $adapter->setObject($object instanceof Provider ? $object->getEntity() : $object);

        return $adapter;

    }

    /**
     * Create a new entity object for adapter
     *
     * @param $object
     * @return Entity|mixed|object
     */
    public function createObject($object)
    {
        // this is very specific to container
        // @coverageIgnoreStart
        if (is_string($object)) {
            $container = FacadeFactory::getContainer();
            if ($container->has($object)) {
                $object = $container->get($object);
            }elseif (class_exists($object)) {
                $object = new $object;
            } else {
                $object = new Provider($object);
            }
        }
        // @coverageIgnoreEnd

        if(!is_object($object)){
            $object = new Entity();
        }

        return $object;
    }

    /**
     * @return AdapterInterface[]
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
        } elseif ($object instanceof \stdClass) {
            $array = (array)$object;
            $hash = md5(json_encode(array_keys($array)));
        }elseif ($object instanceof Provider) {
            $hash = md5($object->getTableName());
        } else {
            $hash = md5(get_class($object));
        }
        return $hash;
    }
}