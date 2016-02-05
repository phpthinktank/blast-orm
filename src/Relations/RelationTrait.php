<?php
/*
*
* (c) Marco Bunge <marco_bunge@web.de>
*
* For the full copyright and license information, please view the LICENSE.txt
* file that was distributed with this source code.
*
* Date: 05.02.2016
* Time: 14:02
*/

namespace Blast\Db\Relations;


use Blast\Db\Entity\EntityInterface;
use Blast\Db\Orm\MapperInterface;

trait RelationTrait
{
    /**
     * @var MapperInterface
     */
    protected $mapper;

    /**
     * @var EntityInterface
     */
    protected $entity;

    /**
     * @var EntityInterface
     */
    protected $foreignEntity;

    /**
     * @var integer|string
     */
    protected $foreignKey;

    /**
     * @var integer|string
     */
    protected $localKey;

    /**
     * @var bool
     */
    protected $foreignEntityUpdate = false;

    /**
     * @return boolean
     */
    public function isForeignEntityUpdate()
    {
        return $this->foreignEntityUpdate;
    }

    /**
     * @param boolean $foreignEntityUpdate
     */
    protected function setForeignEntityUpdate($foreignEntityUpdate)
    {
        $this->foreignEntityUpdate = $foreignEntityUpdate;
    }

    /**
     * @return MapperInterface
     */
    public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * @return EntityInterface
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return EntityInterface
     */
    public function getForeignEntity()
    {
        return $this->foreignEntity;
    }

    /**
     * @param EntityInterface $foreignEntity
     */
    public function setForeignEntity($foreignEntity)
    {
        $this->setForeignEntityUpdate(true);
        $this->foreignEntity = $foreignEntity;
    }

    /**
     * @return int|string
     */
    public function getForeignKey()
    {
        if($this->foreignKey === null){
            $this->foreignKey = $this->getForeignEntity()->getTable()->getPrimaryKeyName();
        }
        return $this->foreignKey;
    }

    /**
     * @return int|string
     */
    public function getLocalKey()
    {
        if($this->localKey === null){
            $this->localKey = $this->getEntity()->getTable()->getPrimaryKeyName();
        }
        return $this->localKey;
    }
}