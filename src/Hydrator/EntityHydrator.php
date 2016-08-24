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

class EntityHydrator implements HydratorInterface
{

    /**
     * @var \Blast\Orm\Entity\Provider
     */
    private $provider;

    public function __construct(ProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Extract values from given object
     *
     * @return array
     */
    public function extract()
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
     * @return object|\SplStack
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

        // hydrate to array object
        if ( method_exists($entity, 'populate') || method_exists($entity, 'exchangeArray') ) {
            $hydrator = $this->getArraySerizableHydrator();
            $entity = $hydrator->hydrate($data, $entity);

            //add relations
            $data = $this->addRelationsToData($data, $entity);
            $entity = $hydrator->hydrate($data, $entity);
        }

        $this->camelizeKeys($data);

        $classMethodHydrator = $this->getClassMethodsHydrator();
        $propertyHydrator = $this->getObjectPropertyHydrator();

        // hydrate entity data
        $entity = $classMethodHydrator->hydrate(
            $data,
            $propertyHydrator->hydrate($data, $entity)
        );

        //add relations
        $data = $this->addRelationsToData($data, $entity);

        // hydrate entity with relation data to add hydrated entity to relation
        return $classMethodHydrator->hydrate(
            $data,
            $propertyHydrator->hydrate($data, $entity)
        );
    }

    /**
     * Add relations to data
     *
     * @param $data
     * @param $entity
     * @return mixed
     */
    protected function addRelationsToData($data, $entity)
    {
        foreach ($this->provider->getDefinition()->getRelations() as $name => $relation) {
            if ( is_numeric($name) ) {
                $name = $relation->getName();
            }
            // disallow overwriting existing data
            if ( isset($data[$name]) ) {
                continue;
            }

            // only attached entity is allowed!
            $entityClass = get_class($entity);
            if($relation->getEntity() instanceof $entityClass){
                $relation->setEntity($entity);
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

    /**
     * Convert keys to underscore
     *
     * @param $data
     * @return array
     */
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
        return new ClassMethods(false);
    }

    /**
     * @return ObjectProperty
     */
    protected function getObjectPropertyHydrator()
    {
        return new ObjectProperty();
    }
}
