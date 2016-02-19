<?php
/*
*
* (c) Marco Bunge <marco_bunge@web.de>
*
* For the full copyright and license information, please view the LICENSE.txt
* file that was distributed with this source code.
*
* Date: 19.02.2016
* Time: 11:20
*/

namespace Blast\Db\Orm;


class MapperHelper
{

    /**
     * @param $name
     * @param $instance
     * @return mixed|null
     * @throws \Exception
     */
    public static function findOption($name, $instance){
        $reflection = new \ReflectionClass($instance);
        $value = null;
        if ($reflection->hasProperty($name)) {
            $property = $reflection->getProperty($name);
            if (!$property->isPublic()) {
                $property->setAccessible(TRUE);
            }
            $value = $property->getValue($instance);
        }elseif($reflection->hasConstant($name) || $reflection->hasConstant(strtoupper($name))){
            $value = $reflection->hasConstant($name) ? $reflection->getConstant($name) : $reflection->getConstant(strtoupper($name));
        }elseif($reflection->hasMethod($name) || $reflection->hasMethod('get' . ucfirst($name))){
            $method = $reflection->hasMethod($name) ? $reflection->getMethod($name) : $reflection->hasMethod('get' . ucfirst($name));
            if(!$method->isPublic()){
                $method->setAccessible(true);
            }
            $value = $method->invoke($instance);
        }else{
            throw new \Exception('Unable to find ' . $name . ' in ' . get_class($instance));
        }

        return $value;
    }
}