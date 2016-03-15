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

use Blast\Orm\Entity\Provider;
use Blast\Orm\Entity\ProviderInterface;
use Blast\Orm\Facades\FacadeFactory;

class Locator implements LocatorInterface
{
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
        $mapperInterface = $this->getProvider($entity)->getMapper();

        return $mapperInterface;
    }

    /**
     * Get adapter for entity
     *
     * @param $entity
     * @return ProviderInterface
     */
    public function getProvider($entity)
    {
        $container = FacadeFactory::getContainer();
        if (!$container->has(ProviderInterface::class)) {
            $container->add(ProviderInterface::class, Provider::class);
        }
        return FacadeFactory::getContainer()->get(ProviderInterface::class, [$entity]);
    }
}
