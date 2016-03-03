<?php
/*
*
* (c) Marco Bunge <marco_bunge@web.de>
*
* For the full copyright and license information, please view the LICENSE.txt
* file that was distributed with this source code.
*
* Date: 02.03.2016
* Time: 22:22
*/

namespace Blast\Orm\Facades;


use Blast\Facades\FacadeFactory as BlastFacadeFactory;
use Blast\Orm\Container\Container;
use Blast\Orm\Container\ContainerAdd;
use Interop\Container\ContainerInterface;

class FacadeFactory extends BlastFacadeFactory
{
    public static function getContainer()
    {
        if(parent::$container === null){
            parent::setContainer(new Container());
        }

        $container = parent::$container;

        if(!$container->has(ContainerInterface::class)){
            ContainerAdd::add($container, ContainerInterface::class, $container);
            parent::$container = $container;
        }

        return parent::getContainer();
    }


}