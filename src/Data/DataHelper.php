<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 12.02.2016
 * Time: 10:02
 *
 */

namespace Blast\Orm\Data;

class DataHelper
{

    /**
     * receive data from object
     *
     * @param $object
     * @return array
     */
    public static function receiveDataFromObject($object)
    {
        $data = [];
        if ($object instanceof DataObjectInterface || $object instanceof ImmutableDataObjectInterface) {
            $data = $object->getData();
        } elseif ($object instanceof \ArrayObject) {
            $data = $object->getArrayCopy();
        } elseif (is_object($object)) {

            $reflection = new \ReflectionObject($object);
            $properties = $reflection->getProperties();
            foreach($properties as $property){
                if($reflection->hasMethod('get' . ucfirst($property->getName()))){ //check if property has a getter
                    $method = $reflection->getMethod('get' . ucfirst($property->getName()));

                    //ignore static
                    if($method->isStatic()){
                        continue;
                    }
                    if(!$method->isPublic()){
                        $method->setAccessible(true);
                    }
                    $value = $method->invoke($object);
                }else{ //make property accessible and get value

                    //ignore static
                    if($property->isStatic()){
                        continue;
                    }
                    if(!$property->isPublic()){
                        $property->setAccessible(true);
                    }

                    $value = $property->getValue($object);
                }

                $data[$property->getName()] = $value;
            }

        }
        return $data;
    }

    /**
     * receive data from object
     *
     * @param $object
     * @param array $data
     * @return array
     */
    public static function replaceDataFromObject($object, $data = [])
    {
        if ($object instanceof DataObjectInterface) {
            $object->setData($data);
        } elseif ($object instanceof ImmutableDataObjectInterface) {
            throw new \InvalidArgumentException('Helper can not replace data. Given object is immutable!');
        } elseif ($object instanceof \ArrayObject) {
            $object->exchangeArray($data);
        } elseif (is_object($object)) {
            $reflection = new \ReflectionObject($object);
            $properties = $reflection->getProperties();
            foreach($properties as $property){
                if(!isset($data[$property->getName()])){
                    continue;
                }
                $value = $data[$property->getName()];

                if($reflection->hasMethod('set' . ucfirst($property->getName()))){ //check if property has a setter
                    $method = $reflection->getMethod('set' . ucfirst($property->getName()));

                    //ignore static
                    if($method->isStatic()){
                        continue;
                    }
                    if(!$method->isPublic()){
                        $method->setAccessible(true);
                    }
                    $method->invokeArgs($object, [$value]);
                }else{ //make property accessible and get value

                    //ignore static
                    if($property->isStatic()){
                        continue;
                    }
                    if(!$property->isPublic()){
                        $property->setAccessible(true);
                    }
                    try{
                        $property->setValue($object, $value);
                    }catch(\ReflectionException $e){
                        $object->{$property->getName()} = $value;
                    }
                }

                unset($data[$property->getName()]);
            }

            //pass remaining data as dynamic property
            //workaround for stdClass
            foreach($data as $key => $value){
                $object->$key = $value;
            }
        }
    }

}