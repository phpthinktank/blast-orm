<?php
/*
*
* (c) Marco Bunge <marco_bunge@web.de>
*
* For the full copyright and license information, please view the LICENSE.txt
* file that was distributed with this source code.
*
* Date: 19.03.2016
* Time: 12:44
*/

namespace Blast\Orm;

use Blast\Orm\Hydrator\HydratorInterface;

abstract class AbstractRepository implements MapperFactoryInterface, RepositoryInterface
{
    
    use MapperFactoryTrait;

    /**
     * Get entity for repository
     * 
     * @return object
     */
    abstract public function getEntity();

    /**
     * Get a collection of all entities
     *
     * @return \SplStack|array
     */
    public function all()
    {
        return $this->createMapper($this->getEntity())->select()->execute(HydratorInterface::HYDRATE_COLLECTION);
    }

    /**
     * Find entity by primary key
     *
     * @param mixed $primaryKey
     * @return \ArrayObject|\stdClass|object
     */
    public function find($primaryKey)
    {
        return $this->createMapper($this->getEntity())->find($primaryKey)->execute(HydratorInterface::HYDRATE_ENTITY);
    }

    /**
     * Save new or existing entity data
     *
     * @param object|array $data
     * @return int|bool
     */
    public function save($data)
    {
        return $this->createMapper($data)->save($data)->execute();
    }
}
