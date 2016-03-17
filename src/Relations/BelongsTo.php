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


use Blast\Orm\ConnectionAwareInterface;
use Blast\Orm\ConnectionAwareTrait;
use Blast\Orm\Entity\ProviderFactoryInterface;
use Blast\Orm\Entity\ProviderFactoryTrait;
use Blast\Orm\Hydrator\HydratorInterface;
use Blast\Orm\Query;

class BelongsTo implements ConnectionAwareInterface, RelationInterface, ProviderFactoryInterface
{
    use ConnectionAwareTrait;
    use ProviderFactoryTrait;
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
     * @param $entity
     * @param $foreignEntity
     * @param null $localKey
     */
    public function __construct($entity, $foreignEntity, $localKey = null)
    {
        $this->entity = $entity;
        $this->foreignEntity = $foreignEntity;
        $this->localKey = $localKey;
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
        $provider = $this->createProvider($this->getEntity());
        $foreignProvider = $this->createProvider($this->getForeignEntity());
        $localKey = $this->getLocalKey();

        if ($localKey === null) {
            $localKey = $foreignProvider->getTableName() . '_' . $foreignProvider->getPrimaryKeyName();
        }

        $data = $provider->fetchData();

        //find primary key
        $primaryKey = $data[$localKey];

        $mapper = $foreignProvider->getMapper()->setConnection($this->getConnection());

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
