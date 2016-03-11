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
use Blast\Orm\Entity\EntityHydratorInterface;
use Blast\Orm\Query;

class HasMany implements RelationInterface
{
    use EntityAdapterLoaderTrait;
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
        $adapter = $this->loadAdapter($this->getEntity());
        $foreignAdapter = $this->loadAdapter($this->getForeignEntity());
        $foreignKey = $this->getForeignKey();

        $data = $adapter->getData();

        //find primary key
        if ($foreignKey === null) {
            $foreignKey = $adapter->getTableName() . '_' . $adapter->getPrimaryKeyName();
        }

        $mapper = $foreignAdapter->getMapper();

        $foreignKeyValue = isset($data[$adapter->getPrimaryKeyName()]) ? $data[$adapter->getPrimaryKeyName()] : false;

        //if no primary key is available, return a select
        $query = $mapper->select();
        if ($foreignKeyValue !== false) {
            $query->where((new Query())->expr()->eq($foreignKey, $foreignKeyValue));

        }
        $this->query = $query;
        $this->name = $foreignAdapter->getTableName();
    }

    /**
     * @return \Blast\Orm\Data\DataObject
     */
    public function execute()
    {
        return $this->getQuery()->execute(EntityHydratorInterface::HYDRATE_COLLECTION);
    }


}