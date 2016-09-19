<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 27.07.2016
 * Time: 11:07
 *
 */

namespace Blast\Orm;


use Doctrine\Common\Cache\Cache;

class Support
{

    /**
     * Get a list of PHP defined classes
     *
     * @param $nameOrObject
     * @return bool
     */
    public static function isPHPInternalClass($nameOrObject){
        return in_array(ltrim((string)static::getClass($nameOrObject), '\\'), spl_classes());
    }

    /**
     * Get class of given FQCN or Object
     *
     * @param $nameOrObject
     * @param bool $strict
     * @return bool|string
     */
    public static function getClass($nameOrObject, $strict = true){
        if(is_string($nameOrObject) && class_exists($nameOrObject)){
            return $nameOrObject;
        }

        if(is_object($nameOrObject)){
            return get_class($nameOrObject);
        }

        return $strict ? false : gettype($nameOrObject);
    }

    /**
     * @param $entity
     * @param Cache $cache
     * @return \ReflectionClass
     */
    public static function getCachedReflectionClass($entity, Cache $cache)
    {
        $entityFQCN = static::getClass($entity);
        $isPHPInternalClass = static::isPHPInternalClass($entityFQCN);

        // load from cache if entity is a valid and no internal php class
        if(false !== $entityFQCN && false === $isPHPInternalClass){
            if($cache->contains($entityFQCN)){
                return $cache->fetch($entityFQCN);
            }
        }

        $reflection = new \ReflectionClass($entity);

        // avoid caching if entity is an internal php class
        if(true === $isPHPInternalClass){
            return $reflection;
        }

        $cache->save($entityFQCN, $reflection);

        return $cache->fetch($entityFQCN);
    }

    /**
     * @param $entity
     * @return bool|string
     */
    public static function getEntityName($entity){
        $fqcn = static::getClass($entity);
        return false === static::isPHPInternalClass($fqcn) && false !== $fqcn ?
            $fqcn : false;
    }

}
