<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 19.09.2016
 * Time: 14:46
 */

namespace Blast\Orm\Locator;


use Interop\Container\Exception\ContainerException;
use Interop\Container\Exception\NotFoundException;

class LocatorException extends \Exception implements NotFoundException, ContainerException
{

    public static function notFoundException($id)
    {
        return new self('unable to locate service ' . $id);
    }

    public static function factoryException($id){
        return new self('unable to create service ' . $id);
    }
}
