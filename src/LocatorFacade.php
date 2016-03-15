<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 07.03.2016
 * Time: 07:43
 *
 */

namespace Blast\Orm;


use Blast\Orm\Facades\AbstractFacade;
use Blast\Orm\Facades\FacadeFactory;

/**
 * Class LocatorFacade
 * @package Blast\Orm
 * @method static \Blast\Orm\Entity\Provider getProvider($entity)
 * @method static \League\Container\ContainerInterface getProviderManager()
 * @method static \Blast\Orm\ConnectionManager getConnectionManager()
 * @method static \Blast\Orm\MapperInterface getMapper($entity)
 */
class LocatorFacade extends AbstractFacade
{
    protected static function accessor()
    {
        $accessor = LocatorInterface::class;

        if (!FacadeFactory::getContainer()->has($accessor)) {
            FacadeFactory::getContainer()->share($accessor, new Locator());
        }

        return $accessor;
    }
}
