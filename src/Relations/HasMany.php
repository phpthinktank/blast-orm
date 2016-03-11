<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 29.02.2016
 * Time: 12:14
 *
 */

namespace Blast\Orm\Relations;


use Blast\Orm\Entity\EntityAdapterLoaderTrait;
use Blast\Orm\HydratorInterface;
use Blast\Orm\LocatorFacade;
use Blast\Orm\Query;

class HasMany implements RelationInterface
{

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
    private $foreignKey;

    /**
     * Local entity relates to many entries of foreign entity by foreign key
     *
     * @param $entity
     * @param $foreignEntity
     * @param null $foreignKey
     */
    public function __construct($entity, $foreignEntity, $foreignKey = null)
    {

        $this->entity = $entity;
        $this->foreignEntity = $foreignEntity;
        $this->foreignKey = $foreignKey;
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
    public function getForeignKey()
    {
        return $this->foreignKey;
    }

    protected function init()
    {
        $provider = LocatorFacade::getProvider($this->getEntity());
        $foreignProvider = LocatorFacade::getProvider($this->getForeignEntity());
        $foreignKey = $this->getForeignKey();

        $data = $adapter->getData();

        //find primary key
        if ($foreignKey === null) {
            $foreignKey = $provider->getTableName() . '_' . $provider->getPrimaryKeyName();
        }

        $mapper = $foreignProvider->getMapper();

        $foreignKeyValue = isset($data[$provider->getPrimaryKeyName()]) ? $data[$provider->getPrimaryKeyName()] : false;

        //if no primary key is available, return a select
        $query = $mapper->select();
        if ($foreignKeyValue !== false) {
            $query->where((new Query())->expr()->eq($foreignKey, $foreignKeyValue));

        }
        $this->query = $query;
        $this->name = $foreignProvider->getTableName();
    }

    /**
     * @return \Blast\Orm\Data\\ArrayObject
     */
    public function execute()
    {
        return $this->getQuery()->execute(HydratorInterface::HYDRATE_COLLECTION);
    }


}