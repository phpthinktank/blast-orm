<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 29.02.2016
 * Time: 10:21
 *
 */

namespace Blast\Orm;

use Blast\Orm\Entity\EntityAwareInterface;
use Blast\Orm\Entity\EntityAwareTrait;
use Blast\Orm\Entity\Provider;
use Blast\Orm\Hydrator\HydratorInterface;

abstract class AbstractRepository implements EntityAwareInterface, RepositoryInterface
{
    use EntityAwareTrait;

    /**
     * @var Provider
     */
    protected $provider = null;

    /**
     * Get adapter for entity
     *
     * @return Provider
     */
    private function getProvider()
    {
        if ($this->provider === null) {
            $this->provider = LocatorFacade::getProvider($this->getEntity());
        }
        return $this->provider;
    }

    /**
     * Get a collection of all entities
     *
     * @return \SplStack|array
     */
    public function all()
    {
        return $this->getProvider()->getMapper()->select()->execute(HydratorInterface::HYDRATE_COLLECTION);
    }

    /**
     * Find entity by primary key
     *
     * @param mixed $primaryKey
     * @return \ArrayObject|\stdClass|object
     */
    public function find($primaryKey)
    {
        return $this->getProvider()->getMapper()->find($primaryKey)->execute(HydratorInterface::HYDRATE_ENTITY);
    }

    /**
     * Save new or existing entity data
     *
     * @param object|array $data
     * @return int|bool
     */
    public function save($data)
    {

        if (is_array($data)) {
            $provider = LocatorFacade::getProvider($this->getEntity());
            $provider->fromArrayToObject($data);
        } else {
            $provider = $this->getProvider();
        }

        $mapper = $this->getProvider()->getMapper();
        $enw = $provider->isNew();
        $query = $enw ? $mapper->create($data) : $mapper->update($data);
        return $query->execute();
    }

}