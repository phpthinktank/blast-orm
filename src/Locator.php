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
use Interop\Container\ContainerInterface;
use League\Container\Container;

class Locator implements LocatorInterface
{

    /**
     * @var \Interop\Container\ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container = null)
    {

        $this->container = new Container();

        if (null !== $container) {
            $this->container->delegate($container);
        }
    }

    /**
     * @return \League\Container\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Get connection manager
     *
     * @return ConnectionManager
     */
    public function getConnectionManager()
    {
        $container = $this->getContainer();
        if (!$container->has(ConnectionManager::class)) {
            $container->share(ConnectionManager::class);
        }
        return $this->container->get(ConnectionManager::class);
    }

    /**
     * Get mapper for entity
     *
     * @param $entity
     * @return MapperInterface
     */
    public function getMapper($entity)
    {
        $container = $this->getContainer();
        if (!$container->has(MapperInterface::class)) {
            $container->add(MapperInterface::class, Mapper::class);
        }
        return $container->get(MapperInterface::class, [$this, $entity]);
    }

    /**
     * Get adapter for entity
     *
     * @param $entity
     * @return ProviderInterface
     */
    public function getProvider($entity)
    {
        $container = $this->getContainer();
        if (!$container->has(ProviderInterface::class)) {
            $container->add(ProviderInterface::class, Provider::class);
        }
        return $this->container->get(ProviderInterface::class, [$this, $entity]);
    }
}
