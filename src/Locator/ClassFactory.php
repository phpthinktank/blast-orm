<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 19.09.2016
 * Time: 15:05
 */

namespace Blast\Orm\Locator;


class ClassFactory implements FactoryInterface
{

    public function create($class, LocatorInterface $locator, array $arguments = [])
    {
        if(!class_exists($class)){
            throw LocatorException::factoryException($class);
        }

        return (new \ReflectionClass($class))->newInstanceArgs($arguments);
    }
}
