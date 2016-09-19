<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 19.09.2016
 * Time: 15:02
 */

namespace Blast\Orm\Locator;


interface FactoryInterface
{

    /**
     * @param string $class Service id
     * @param LocatorInterface $locator
     * @param array $arguments
     * @return mixed
     */
    public function create($class, LocatorInterface $locator, array $arguments = []);

}
