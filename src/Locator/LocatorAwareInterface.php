<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 19.09.2016
 * Time: 13:37
 */

namespace Blast\Orm\Locator;


interface LocatorAwareInterface
{

    /**
     * @return LocatorInterface
     */
    public function getLocator();

}
