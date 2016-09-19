<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 19.09.2016
 * Time: 13:16
 */

namespace Blast\Orm\Locator;


use Blast\Orm\QueryInterface;
use Interop\Container\ContainerInterface;

interface LocatorInterface extends ContainerInterface
{

    /**
     * @param ContainerInterface $delegate
     * @return $this
     */
    public function delegate(ContainerInterface $delegate);

}
