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


use Blast\Orm\Entity\EntityAdapterInterface;
use Blast\Orm\Entity\EntityAdapterManager;

interface LocatorInterface
{

    /**
     * Get adapter for entity
     *
     * @param $entity
     * @return EntityAdapterInterface
     */
    public function getAdapter($entity);

    /**
     * Get adapter manager
     *
     * @return EntityAdapterManager
     */
    public function getAdapterManager();

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
     * @return EntityAdapterInterface
     */
    public function getMapper($entity);

}