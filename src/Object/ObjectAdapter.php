<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 24.02.2016
 * Time: 11:04
 *
 */

namespace Blast\Orm\Object;


use ReflectionClass;
use ReflectionObject;

class ObjectAdapter implements ObjectAdapterInterface
{

    const IS_PROPERTY = 256;
    const IS_METHOD = 512;
    const IS_CONSTANT = 1024;

    /**
     * @var object
     */
    private $object = null;

    /**
     * @var ReflectionObject
     */
    private $reflection = null;

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
     * @return ReflectionObject
     */
    public function getReflection()
    {
        if($this->reflection === null){
            $this->reflection = new ReflectionObject($this->getObject());
        }
        return $this->reflection;
    }



    /**
     * return value of method or property.
     *
     * If property has a getter, return getter value instead of property value
     *
     * @param string $name
     * @param mixed $default
     * @param null $instance
     * @param int $filter
     * @param int $only
     * @return mixed|null
     */
    public function access($name, $default = null, $instance = null, $filter = 0, $only = 0)
    {
        if ($instance === null) {
            $instance = $this->getObject();
        }
        $reflection = $this->getReflection();

        if (!$this->canAccess($reflection, $name, $filter)) {
            return false;
        }

        $value = null;

        $visitMethod = 'get' . ucfirst($name);
        if (
            //return value of method or property getter but ignore constants
            ($reflection->hasMethod($name) && $only & static::IS_METHOD) ||
            (
                $only & static::IS_PROPERTY &&
                $reflection->hasMethod($visitMethod) && !(
                    $reflection->hasConstant($name) || $reflection->hasConstant(strtoupper($name))
                )
            )
        ) {
            $method = $reflection->hasMethod($name) ? $reflection->getMethod($name) : $reflection->getMethod($visitMethod);
            if (!$method->isPublic()) {
                $method->setAccessible(true);
            }
            $value = $method->invoke($instance);
        } elseif ($only & static::IS_CONSTANT && ($reflection->hasConstant($name) || $reflection->hasConstant(strtoupper($name)))) {
            $value = $reflection->hasConstant($name) ? $reflection->getConstant($name) : $reflection->getConstant(strtoupper($name));
        } elseif ($only & static::IS_PROPERTY && $reflection->hasProperty($name)) {
            $property = $reflection->getProperty($name);
            if (!$property->isPublic()) {
                $property->setAccessible(TRUE);
            }
            $value = $property->getValue($instance);
        } else {
            $value = $default;
        }

        return $value;
    }

    /**
     * return value of a method or property or a property getter
     * @param $name
     * @param mixed $value
     * @param null $instance
     * @param int $filter
     * @param int $only
     * @return mixed|null
     */
    public function mutate($name, $value = null, $instance = null, $filter = 0, $only = 0)
    {
        if ($instance === null) {
            $instance = $this->getObject();
        }

        if ($only === 0) {
            $only = static::IS_METHOD | static::IS_PROPERTY;
        }

        $reflection = $this->getReflection();

        if (!$this->canAccess($reflection, $name, $filter)) {
            return false;
        }

        $visitMethod = 'set' . ucfirst($name);

        if (
            ($reflection->hasMethod($name) && $only & static::IS_METHOD) ||
            ($only & static::IS_PROPERTY && $reflection->hasMethod($visitMethod) &&
                $this->canAccess($reflection, $visitMethod, $filter, static::IS_METHOD)
            )
        ) {
            $method = $reflection->hasMethod($name) ? $reflection->getMethod($name) : $reflection->getMethod($visitMethod);
            if (!$method->isPublic()) {
                $method->setAccessible(true);
            }
            $method->invoke($instance, $value);
        } elseif ($reflection->hasProperty($name) && $only & static::IS_PROPERTY) {
            $property = $reflection->getProperty($name);
            if (!$property->isPublic()) {
                $property->setAccessible(TRUE);
            }
            try{
                $property->setValue($instance, $value);
            }catch(\ReflectionException $e){
                $instance->{$property->getName()} = $value;
            }
        } else {
            if ($only & static::IS_PROPERTY) {
                $instance->$name = $value;
            }
        }

        return $this;
    }

    /**
     * Check if method or property is allowed to access
     * @param ReflectionClass $reflection
     * @param $name
     * @param $options
     * @param int $only
     * @return bool
     */
    protected function canAccess(ReflectionClass $reflection, $name, $options, $only = 0)
    {

        if ($only === 0) {
            $only = static::IS_METHOD | static::IS_PROPERTY;
        }

        $methods = $reflection->getMethods($options);
        $properties = $reflection->getProperties($options);

        $cond = false;

        if ($only & static::IS_METHOD) {
            $cond = $cond || isset($methods[$name]);
        }

        if ($only & static::IS_PROPERTY) {
            $cond = $cond || isset($properties[$name]);
        }

        return $cond;
    }

    protected function reset(){
        $this->setObject($this->getReflection()->newInstance());
        $this->reflection = null;
    }
}