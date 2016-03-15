<?php
/*
*
* (c) Marco Bunge <marco_bunge@web.de>
*
* For the full copyright and license information, please view the LICENSE.txt
* file that was distributed with this source code.
*
* Date: 12.03.2016
* Time: 16:54
*/

namespace Blast\Orm\Hydrator;

use Adamlc\LetterCase\LetterCase;
use Blast\Orm\LocatorFacade;

class ObjectToArrayHydrator implements HydratorInterface
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

        if ($this->entity instanceof \ArrayObject) {
            $arrayCopy = $this->entity->getArrayCopy();
            $data = array_merge($data, $arrayCopy);
        }

        $reflection = new \ReflectionObject($this->entity);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        $arrayReflection = new \ReflectionClass(\ArrayObject::class);

        foreach ($properties as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $value = $property->getValue($this->entity);

            if (isset($data[$property->getName()]) && null === $value) {
                continue;
            }

            $data[$property->getName()] = $value;
        }

        foreach ($methods as $name => $method) {
            if (
            $arrayReflection->hasMethod($method->getName())
            ) {
                continue;
            }
            //remove get name
            $valid = substr($method->getName(), 0, 3);
            $key = substr($method->getName(), 3);
            if (
                $method->isStatic() ||
                $valid ||
                0 !== strlen($key)
            ) {
                continue;
            }

            $fieldName = (new LetterCase())->snake(substr($method->getName(), 3));
            $value = $method->invoke($this->entity);

            if (isset($data[$fieldName]) && null === $value) {
                continue;
            }

            $data[$fieldName] = $value;
        }

        return $data;
    }
}

