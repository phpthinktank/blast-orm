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
        $this->entity = LocatorFacade::getProvider($entity);
    }

    /**
     * @param array $data
     * @param string $option
     * @return mixed
     */
    public function hydrate($data = [], $option = self::HYDRATE_AUTO)
    {
        $reflection = new \ReflectionObject($this->entity);

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
                $method->invokeArgs($this->entity, [$data[$fieldName]]);
            }

        }
    }
}