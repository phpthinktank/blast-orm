<?php
/*
*
* (c) Marco Bunge <marco_bunge@web.de>
*
* For the full copyright and license information, please view the LICENSE.txt
* file that was distributed with this source code.
*
* Date: 11.03.2016
* Time: 23:24
*/

namespace Blast\Orm\Hydrator;

use Blast\Orm\Entity\ProviderInterface;
use Doctrine\Common\Inflector\Inflector;
use Doctrine\DBAL\Driver\Statement;
use Zend\Hydrator\ArraySerializable;
use Zend\Hydrator\ClassMethods;
use Zend\Hydrator\ObjectProperty;

class Hydrator implements HydratorInterface
{

    /**
     * @var \Blast\Orm\Entity\Provider
     */
    private $provider;

    public function __construct(ProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    public function extract($object)
    {
        $entity = clone $this->provider->getEntity();

        // extract from array object
        // ignore further extraction strategy
        if ( method_exists($entity, 'populate') || method_exists($entity, 'exchangeArray') ) {
            $hydrator = $this->getArraySerizableHydrator();
            return $this->underscorizeKeys($hydrator->extract($entity));
        }

        // extract data from class getters and properties
        $propertyHydrator = $this->getObjectPropertyHydrator();
        $classMethodHydrator = $this->getClassMethodsHydrator();

        return array_replace(
            $this->underscorizeKeys($propertyHydrator->extract($entity)),
            $this->underscorizeKeys($classMethodHydrator->extract($entity))
        );
    }

    /**
     * @param array $data
     * @param string $option
     * @return mixed
     */
    public function hydrate($data = [], $option = self::HYDRATE_AUTO)
    {
        $option = $this->determineOption($data, $option);

        switch ($option) {
            case self::HYDRATE_RAW:
                return $data;
            //if entity set has many items, return a collection of entities
            case self::HYDRATE_COLLECTION :
                return $this->hydrateCollection($data);
            //if entity has one item, return the entity
            case self::HYDRATE_ENTITY:
                $data = $this->isCollectable($data) ? array_shift($data) : $data;

                return $this->hydrateEntity($data);
        }

        throw new \LogicException('Unknown option ' . $option);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function isCollectable($data)
    {
        if ( ! is_array($data) ) {
            return false;
        }

        return is_array(reset($data));
    }

    /**
     * @param $data
     * @param $option
     * @return string
     */
    protected function determineOption($data, $option)
    {
        if ( $option === self::HYDRATE_RAW ||
            $data instanceof Statement ||
            is_scalar($data) ||
            is_bool($data) ||
            null === $data
        ) {
            return self::HYDRATE_RAW;
        }
        if ( $option === self::HYDRATE_AUTO ) {
            $option = $this->isCollectable($data) && (count($data) === 0 || count($data) > 1) ? self::HYDRATE_COLLECTION : self::HYDRATE_ENTITY;
        }

        return $option;
    }

    /**
     * @param $data
     * @return array
     */
    protected function hydrateCollection($data)
    {
        $stack = $this->provider->getDefinition()->getEntityCollection();
        foreach ($data as $key => $value) {
            $stack->push($this->hydrateEntity($value));
        }

        $stack->rewind();

        return $stack;
    }

    /**
     * Hydrates data to an entity
     *
     * @param $data
     * @return array|\ArrayObject|object|\stdClass
     */
    protected function hydrateEntity($data)
    {
        $entity = clone $this->provider->getEntity();

        //add relations
        $data = $this->addRelationsToData($data);

        // hydrate to array object
        if ( method_exists($entity, 'populate') || method_exists($entity, 'exchangeArray') ) {
            $hydrator = $this->getArraySerizableHydrator();
            $entity = $hydrator->hydrate($data, $entity);
        }

        $this->camelizeKeys($data);

        $classMethodHydrator = $this->getClassMethodsHydrator();
        $propertyHydrator = $this->getObjectPropertyHydrator();

        return $classMethodHydrator->hydrate(
            $data,
            $propertyHydrator->hydrate($data, $entity)
        );
    }

    /**
     * Add relations to data
     *
     * @param $data
     * @return mixed
     */
    protected function addRelationsToData($data)
    {
        foreach ($this->provider->getDefinition()->getRelations() as $name => $relation) {
            if ( is_numeric($name) ) {
                $name = $relation->getName();
            }
            // disallow overwriting existing data
            if ( isset($data[$name]) ) {
                continue;
            }
            $data[$name] = $relation;
        }

        return $data;
    }

    /**
     * Convert keys to camel case
     *
     * @param $data
     * @return array
     */
    protected function camelizeKeys($data)
    {
        $result = [];
        foreach ($data as $key => $value) {
            $result[Inflector::camelize($key)] = $value;
        }

        return $result;
    }

    protected function underscorizeKeys($data)
    {
        $result = [];
        foreach ($data as $key => $value) {
            $result[Inflector::tableize($key)] = $value;
        }

        return $result;
    }

    /**
     * @return ArraySerializable
     */
    protected function getArraySerizableHydrator()
    {
        return new ArraySerializable();
    }

    /**
     * @return ClassMethods
     */
    protected function getClassMethodsHydrator()
    {
        $hydrator = new ClassMethods(false);
//        $hydrator->addFilter('disallowArrayObjectMethods', function ($property) {
//
//            $arrayObjectClass = \ArrayObject::class;
//            $class = '';
//            $key = '';
//
//            $delimiterPos = strpos($property, '::');
//            if ( $delimiterPos !== false ) {
//                $class = substr($property, 0, $delimiterPos);
//                $key = substr($property, $delimiterPos+2);
//            }else{
//                $key = $property;
//            }
//
//            // valid if class is no instance of array object
//            if (            ! (
//                is_subclass_of($class, $arrayObjectClass) ||
//                trim($arrayObjectClass, '\\') === trim($class, '\\')
//            )) {
//                return true;
//            }
//
//            $disallowed = get_class_methods($arrayObjectClass);
//            $method = Inflector::camelize($key);
//
//            $result = ! (
//                in_array($method, $disallowed) ||
//                in_array('get' . ucfirst($method), $disallowed)
//            );
//
//            if ( true ) {
//
//            }
//
//            return $result;
//        });
//
//        $hydrator->removeFilter('parameter');
//        $hydrator->removeFilter('has');
//        $hydrator->removeFilter('is');

        return $hydrator;
    }

    /**
     * @return ObjectProperty
     */
    protected function getObjectPropertyHydrator()
    {
        return new ObjectProperty();
    }
}
