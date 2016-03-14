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
use Interop\Container\ContainerInterface;
use League\Container\Container;

class FacadeFactory extends BlastFacadeFactory
{
    /**
     * @return Container
     * @throws \Exception
     */
    public static function getContainer()
    {
        if(parent::$container === null){
            parent::setContainer(new Container());
        }

        if(!(parent::$container instanceof \League\Container\ContainerInterface)){
            $container = new Container();
            $container->delegate(parent::$container);
            parent::setContainer($container);
        }

        /**
         * @var Container
         */
        $container = parent::$container;

        if(!$container->has(ContainerInterface::class) && $container instanceof Container){
            $container->add(ContainerInterface::class, $container);
            parent::setContainer($container);
        }

        return parent::getContainer();
    }

    /**
     * @param ContainerInterface|Container $container
     */
    public static function setContainer(ContainerInterface $container)
    {
        parent::setContainer($container);
    }


}