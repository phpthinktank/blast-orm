<?php
/*
*
* (c) Marco Bunge <marco_bunge@web.de>
*
* For the full copyright and license information, please view the LICENSE.txt
* file that was distributed with this source code.
*
* Date: 02.03.2016
* Time: 22:26
*/

namespace Blast\Orm\Facades;


class AbstractFacade extends \Blast\Facades\AbstractFacade
{

    /**
     * Call from attached instance
     * @param $name
     * @param array $arguments
     * @return mixed
     * @throws \Exception
     */
    public static function __callStatic($name, array $arguments = [])
    {
        return FacadeFactory::create(static::accessor(), $name, $arguments);
    }

    /**
     * Get attached instance
     * @return mixed
     * @throws \Exception
     */
    public static function __instance()
    {
        return FacadeFactory::getContainer()->get(static::accessor());
    }

}