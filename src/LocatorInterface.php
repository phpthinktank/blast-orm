<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 07.03.2016
 * Time: 07:45
 *
 */

namespace Blast\Orm;

use Blast\Orm\Entity\ProviderInterface;

interface LocatorInterface
{

    /**
     * Get adapter for entity
     *
     * @param $entity
     *
     * @return ProviderInterface
     */
    public function getProvider($entity);

    /**
     * Get connection manager
     *
     * @return ConnectionManager
     */
    public function getConnectionManager();

    /**
     * Get mapper for entity
     *
     * @param $entity
     *
     * @return MapperInterface
     */
    public function getMapper($entity);

}
