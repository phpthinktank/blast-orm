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


use Blast\Orm\Entity\EntityAdapter;
use Blast\Orm\Entity\EntityAdapterManager;
use Blast\Orm\Entity\EntityAdapterInterface;
use Blast\Orm\Facades\FacadeFactory;

class Locator implements LocatorInterface
{
    /**
     * Get adapter for entity
     *
     * @param $entity
     * @return EntityAdapterInterface
     */
    public function getAdapter($entity)
    {
        return $this->getAdapterManager()->get($entity);
    }

    /**
     * Get adapter manager
     *
     * @return EntityAdapterManager
     */
    public function getAdapterManager()
    {
        $container = FacadeFactory::getContainer();
        if (!$container->has(EntityAdapterManager::class)) {
            $container->share(EntityAdapterManager::class);
        }
        return FacadeFactory::getContainer()->get(EntityAdapterManager::class);
    }

    /**
     * Get connection manager
     *
     * @return ConnectionManager
     */
    public function getConnectionManager()
    {
        $container = FacadeFactory::getContainer();
        if (!$container->has(ConnectionManager::class)) {
            $container->share(ConnectionManager::class);
        }
        return FacadeFactory::getContainer()->get(ConnectionManager::class);
    }

    /**
     * Get mapper for entity
     *
     * @param $entity
     * @return MapperInterface
     */
    public function getMapper($entity)
    {
        return $this->getAdapter($entity)->getMapper();
    }
}