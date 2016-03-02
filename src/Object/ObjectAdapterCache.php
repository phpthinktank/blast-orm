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


class ObjectAdapterCache
{
    /**
     * @var ObjectAdapterInterface[]
     */
    private static $adapters = [];

    /**
     * @param null $object
     * @param null $adapterClassName
     * @return ObjectAdapterInterface|static
     * @throws \Exception
     */
    public static function load($object, $adapterClassName = null)
    {
        $object = static::createObject($object);

        if ($object instanceof \ArrayObject) {
            $hash = md5(json_encode(array_keys($object->getArrayCopy())));
        } elseif ($object instanceof \stdClass) {
            $array = (array)$object;
            $hash = md5(json_encode(array_keys($array)));
        } else {
            $hash = md5(get_class($object));
        }

        if (!isset($adapters[$hash])) {
            if(!is_subclass_of($adapterClassName, ObjectAdapterInterface::class)){
                throw new \InvalidArgumentException($adapterClassName . ' needs to be an instance of ' . ObjectAdapterInterface::class);
            }
            $adapter = new $adapterClassName($object);
            static::$adapters[$hash] = $adapter;
        } elseif (isset($adapters[$hash])) {
            $adapter = static::$adapters[$hash];
            $adapter->setObject($object);
        } else {
            throw new \Exception('Unable to load object from adapter');
        }

        gc_collect_cycles();

        return $adapter;

    }

    /**
     * @param $object
     * @return object
     */
    public static function createObject($object)
    {
        if (is_string($object)) {
            if (class_exists($object)) {
                $object = new $object;
            } else {
                throw new \InvalidArgumentException('Unable to create object from string: ' . $object);
            }
        }
        return $object;
    }

    /**
     * @return ObjectAdapterInterface[]
     */
    public static function getAdapters()
    {
        return self::$adapters;
    }
}