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

class FacadeFactory extends BlastFacadeFactory
{
    public static function getContainer()
    {
        if(parent::$container === null){
            parent::$container = new Container();
        }

        return parent::getContainer();
    }
}