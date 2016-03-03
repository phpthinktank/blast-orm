<?php
/*
*
* (c) Marco Bunge <marco_bunge@web.de>
*
* For the full copyright and license information, please view the LICENSE.txt
* file that was distributed with this source code.
*
* Date: 02.03.2016
* Time: 22:38
*/

namespace Blast\Orm;


use Blast\Orm\Facades\AbstractFacade;
use Blast\Orm\Facades\FacadeFactory;

/**
 * @method static void __destruct()
 * @method static ConnectionCollectionInterface addConnection($connection, $name = ConnectionCollection::DEFAULT_CONNECTION)
 * @method static ConnectionCollectionInterface setDefaultConnection($name)
 * @method static array getPreviousConnections()
 * @method static \Doctrine\DBAL\Connection getConnection($name = NULL)
 * @method static bool hasConnection($name)
 * @method static ConnectionCollectionInterface[] getConnections()
 */
class ConnectionFacade extends AbstractFacade
{
    protected static function accessor()
    {
        $container = FacadeFactory::getContainer();
        if(!$container->has(ConnectionCollectionInterface::class) && method_exists($container, 'add')){
            $container->add(ConnectionCollectionInterface::class, new ConnectionCollection(), true);
        }

        return ConnectionCollectionInterface::class;
    }


}