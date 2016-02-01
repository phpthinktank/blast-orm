<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 01.02.2016
 * Time: 15:05
 *
 */

namespace Blast\Db\Relations;


use Blast\Db\Entity\EntityInterface;
use Blast\Db\Orm\MapperInterface;

abstract class AbstractRelation
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

    abstract public function save();

    abstract public function fetch();

}