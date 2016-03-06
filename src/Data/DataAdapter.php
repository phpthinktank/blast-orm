<?php
/*
*
* (c) Marco Bunge <marco_bunge@web.de>
*
* For the full copyright and license information, please view the LICENSE.txt
* file that was distributed with this source code.
*
* Date: 06.03.2016
* Time: 21:12
*/

namespace Blast\Orm\Data;


class DataAdapter implements DataObjectInterface
{

    const DATA_DEFAULT_VALUE = '_____DATA_DEFAULT_VALUE_____';

    /**
     * @var object
     */
    private $object = null;

    /**
     * @var \ReflectionObject
     */
    protected $reflection = null;

    /**
     * EntityAdapter constructor.
     * @param array|\stdClass|\ArrayObject|object|string $object
     */
    public function __construct($object = null)
    {
        $this->setObject($object);
    }

    /**
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param string|object $class
     * @return $this
     */
    public function setObject($class)
    {
        if (is_string($class)) {
            $class = (new \ReflectionClass($class))->newInstance();
        }

        $this->object = $class;

        return $this;
    }

    /**
     * @return \ReflectionObject
     */
    public function getReflection()
    {
        if ($this->reflection === null) {
            $this->reflection = new \ReflectionObject($this->getObject());
        }

        return $this->reflection;
    }

    /**
     * Receive data
     * @return array
     */
    public function getData()
    {

        $data = [];
        $source = $this->getObject();
        $reflection = new \ReflectionObject($source);
        if ($reflection->hasMethod('getData')) {
            $data = $source->getData();
        } elseif ($reflection->hasMethod('data')) {
            $data = $source->data();
        } elseif ($reflection->hasMethod('getArrayCopy')) {
            $data = $source->getArrayCopy();
        } elseif (is_object($source)) {
            $properties = $reflection->getProperties();
            foreach ($properties as $property) {

                $value = $this->get($property->getName(), static::DATA_DEFAULT_VALUE);

                if ($value !== static::DATA_DEFAULT_VALUE) {
                    $data[$property->getName()] = $value;
                }
            }

        }

        return $data;
    }

    /**
     * Replace data
     * @param array $data
     * @return $this
     */
    public function setData($data = [])
    {
        $source = $this->getObject();
        $reflection = new \ReflectionObject($source);
        if ($reflection->hasMethod('setData')) {
            $source->setData($data);
        } elseif ($reflection->hasMethod('data') && count($reflection->getMethod('data')->getParameters()) > 0) {
            $source->data($data);
        } elseif ($reflection->hasMethod('exchangeArray')) {
            $source->exchangeArray($data);
        } elseif (is_object($source)) {
            $properties = $reflection->getProperties();
            foreach ($properties as $property) {
                if (!isset($data[$property->getName()])) {
                    continue;
                }

                $this->set($property->getName(), $data[$property->getName()]);
            }

        }

        return $this;
    }

    /**
     * Call a public method or property from entity.
     *
     * @param $methodOrProperty
     * @param $default
     * @param array $args
     * @param $prefix
     * @param bool $isStatic
     * @return mixed
     */
    public function call($methodOrProperty, $default = null, $args = [], $prefix = null, $isStatic = false)
    {
        $reflection = $this->getReflection();
        $value = $default;
        $prefixed = null === $prefix ? null : $prefix . ucfirst($methodOrProperty);
        if ($reflection->hasMethod($methodOrProperty) || $reflection->hasMethod($prefixed)) {
            $method = $reflection->hasMethod($methodOrProperty) ? $reflection->getMethod($methodOrProperty) : $reflection->getMethod($prefixed);
            $cond = $isStatic ? $method->isStatic() : true;
            $value = $cond && $method->isPublic() ? $method->invokeArgs($this->getObject(),
                $args) : $value;
        } elseif ($reflection->hasProperty($methodOrProperty)) {
            $property = $reflection->getProperty($methodOrProperty);
            $cond = $isStatic ? $property->isStatic() : true;
            if ($cond && $property->isPublic()) {
                $value = $property->getValue($this->getObject());
                if (!empty($args)) {
                    if (is_callable($value)) {
                        $value = call_user_func_array($value, $args);
                    } else {
                        $property->setValue($args);
                        $value = $args;
                    }
                }
            }
        }

        return $value;
    }

    /**
     * Get an entity field value
     *
     * @param $field
     * @param null $default
     * @return mixed
     */
    public function get($field, $default = null)
    {

        $value = $this->call($field, static::DATA_DEFAULT_VALUE, [], 'get');

        if (self::DATA_DEFAULT_VALUE !== $value) {
            return $value;
        }

        $value = $this->call('get', static::DATA_DEFAULT_VALUE, [$field]);

        if (self::DATA_DEFAULT_VALUE !== $value) {
            return $value;
        }

        $value = $this->call('data', static::DATA_DEFAULT_VALUE, [], 'get');

        if (!is_array($value)) {
            return $default;
        }

        if (isset($value[$field])) {
            return $value[$field];
        }

        return $default;
    }

    /**
     * Set an entity field value
     *
     * @param $field
     * @param null $value
     * @return mixed
     */
    public function set($field, $value = null)
    {

        $this->call($field, null, [$value], 'set', false);

        if ($this->get($field) === $value) {
            return $this;
        }

        $this->call('set', null, [$field, $value]);

        if ($this->get($field) === $value) {
            return $value;
        }

        $data = $this->call('data', null, [], 'get');

        if (!is_array($data)) {
            return $this;
        }

        $data[$field] = $value;

        $this->call('data', null, [$data], 'set');

        return $this;
    }
}