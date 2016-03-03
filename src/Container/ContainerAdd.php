<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 03.03.2016
 * Time: 09:25
 *
 */

namespace Blast\Orm\Container;


use Interop\Container\ContainerInterface;

/**
 * Container bridge is ensuring compatibility to `Interop\Container\ContainerInterface`
 * and provide a consistent implementation for adding services
 *
 * Class ContainerBridge
 * @package Blast\Orm\Container
 */
class ContainerAdd
{
    /**
     * Support adding with
     *
     *  - add
     *  - bind
     *  - offsetSet
     *  - set
     *
     * @param ContainerInterface $container
     * @param $id
     * @param null $service
     * @param bool $singleton
     * @return mixed
     * @throws \Exception
     */
    public static function add(ContainerInterface $container, $id, $service = null, $singleton = false){
        $reflection = new \ReflectionObject($container);
        $methodName = null;
        if($reflection->hasMethod('add')){
            $methodName = 'add';
        }elseif($reflection->hasMethod('bind')){
            $methodName = 'bind';
        }elseif($reflection->hasMethod('offsetSet')){
            $methodName = 'offsetSet';
        }elseif($reflection->hasMethod('set')){
            $methodName = 'set';
        }else{
            throw new \Exception('Unsupported container implementation ' . $reflection->getName());
        }

        $methodArgs = [$id, $service];
        $method = $reflection->getMethod($methodName);

        if($method->getNumberOfParameters() === 3){
            $methodArgs[] = $singleton;
        }

        return $method->invokeArgs($container, $methodArgs);
    }
}