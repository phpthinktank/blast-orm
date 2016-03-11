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

namespace Blast\Orm;


use Blast\Facades\FacadeFactory;

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
        $count = count($data);
        if ($option === self::HYDRATE_AUTO) {
            $option = $count > 1 || $count === 0 ? self::HYDRATE_COLLECTION : self::HYDRATE_ENTITY;
        }

        if ($option === self::HYDRATE_COLLECTION) { //if entity set has many items, return a collection of entities
            $stack = [];
            foreach ($data as $key => $value) {
                $stack[$key] = ($this->hydrate($value, self::HYDRATE_ENTITY));
            }
            return $stack;
        } elseif ($option === self::HYDRATE_ENTITY) { //if entity has one item, return the entity

            //does nothing at the moment

        }else{
            throw new \LogicException('Unknown option');
        }

        $entity = clone $this->entity;

        if($entity instanceof \ArrayObject){
            $entity->exchangeArray($data);
            return $entity;
        }

        $reflection = new \ReflectionObject($entity);

        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach($methods as $method){
            if(
                $method->isStatic() ||
                false === strpos($method->getName(), 'set') ||
                4 > strlen($method->getName()) ||
                0 === $method->getNumberOfParameters()
            ){
                continue;
            }

            $key = str_replace('set', '', $method->getName());
            $fieldName = ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $key)), '_');

            if(isset($data[$fieldName])){
                $method->invokeArgs($entity, [$data[$fieldName]]);
            }
        }

        return $entity;
    }
}