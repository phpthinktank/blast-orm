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

use Adamlc\LetterCase\LetterCase;
use Blast\Orm\LocatorFacade;
use Doctrine\DBAL\Driver\Statement;

class ArrayToObjectHydrator implements HydratorInterface
{

    /**
     * @var
     */
    private $entity;

    public function __construct($entity)
    {
        $this->entity = LocatorFacade::getProvider($entity)->getEntity();
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
     * Hydrates data to an entity
     *
     * @param $data
     * @return array|\ArrayObject|object|\stdClass
     */
    protected function hydrateEntity($data)
    {
        $entity = clone $this->entity;

        if ($entity instanceof \ArrayObject) {
            $entity->exchangeArray($data);
        }

        $reflection = new \ReflectionObject($entity);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        $arrayReflection = new \ReflectionClass(\ArrayObject::class);

        foreach ($properties as $property) {
            if ($property->isStatic() || isset($data[$property->getName()])) {
                continue;
            }

            $fieldName = $property->getName();
            if (isset($data[$fieldName])) {
                $property->setValue($this->entity, $data[$fieldName]);
            }
        }

        foreach ($methods as $method) {
            //remove get name
            $valid = substr($method->getName(), 0, 3);
            $key = substr($method->getName(), 3);
            if (
                $method->isStatic() ||
                $valid ||
                0 !== strlen($key) ||
                $arrayReflection->hasMethod($method->getName()) ||
                0 === $method->getNumberOfParameters()
            ) {
                continue;
            }

            $fieldName = (new LetterCase())->snake(str_replace('set', '', $method->getName()));

            if (isset($data[$fieldName])) {
                $method->invokeArgs($entity, [$data[$fieldName]]);
            }
        }

        return $entity;
    }

    /**
     * @param $data
     * @return array
     */
    protected function hydrateCollection($data)
    {
        $stack = new \SplStack();
        foreach ($data as $key => $value) {
            $stack->push($this->hydrateEntity($value));
        }

        $stack->rewind();

        return $stack;
    }

    /**
     * @param $data
     * @param $option
     * @return string
     */
    protected function determineOption($data, $option)
    {
        if ($option === self::HYDRATE_RAW ||
            $data instanceof Statement ||
            is_scalar($data) ||
            is_bool($data) ||
            null === $data
        ) {
            return self::HYDRATE_RAW;
        }
        if ($option === self::HYDRATE_AUTO) {
            $option = $this->isCollectable($data) && (count($data) === 0 || count($data) > 1) ? self::HYDRATE_COLLECTION : self::HYDRATE_ENTITY;
        }

        return $option;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function isCollectable($data)
    {
        if(!is_array($data)){
            return false;
        }
        return is_array(reset($data));
    }
}