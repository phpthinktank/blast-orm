<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 29.02.2016
 * Time: 12:04
 *
 */

namespace Blast\Orm\Relations;


use Blast\Orm\Hydrator\HydratorInterface;
use Blast\Orm\LocatorAwareTrait;
use Blast\Orm\Query;

class BelongsTo implements RelationInterface
{
    use LocatorAwareTrait;
    use RelationTrait;

    /**
     * @var
     */
    private $entity;
    /**
     * @var
     */
    private $foreignEntity;
    /**
     * @var null
     */
    private $localKey;

    /**
     * Local entity belongs to foreign entity by local key
     *
     * @param $locator
     * @param $entity
     * @param $foreignEntity
     * @param null $localKey
     */
    public function __construct($locator, $entity, $foreignEntity, $localKey = null)
    {
        $this->entity = $entity;
        $this->foreignEntity = $foreignEntity;
        $this->localKey = $localKey;
        $this->locator = $locator;
    }

    /**
     * @return array|\ArrayObject|object|bool
     */
    public function execute()
    {
        return $this->getQuery()->execute(HydratorInterface::HYDRATE_AUTO);
    }

    protected function init()
    {
        $provider = $this->getLocator()->getProvider($this->getEntity());
        $foreignProvider = $this->getLocator()->getProvider($this->getForeignEntity());
        $localKey = $this->getLocalKey();

        if ($localKey === null) {
            $localKey = $foreignProvider->getTableName() . '_' . $foreignProvider->getPrimaryKeyName();
        }

        $data = $provider->fromObjectToArray();

        //find primary key
        $primaryKey = $data[$localKey];

        $mapper = $foreignProvider->getMapper();

        //if no primary key is available, return a select
        $this->query = $primaryKey === null ? $mapper->select() : $mapper->find($primaryKey);
        $this->name = $foreignProvider->getTableName();

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return mixed
     */
    public function getForeignEntity()
    {
        return $this->foreignEntity;
    }

    /**
     * @return null
     */
    public function getLocalKey()
    {
        return $this->localKey;
    }

}
