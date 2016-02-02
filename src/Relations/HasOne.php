<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 01.02.2016
 * Time: 15:54
 *
 */

namespace Blast\Db\Relations;


use Blast\Db\Entity\EntityInterface;

class HasOne extends AbstractRelation
{
    /**
     * BelongsTo constructor.
     * @param EntityInterface $entity current entity instance
     * @param string|EntityInterface $foreignEntity Entity which have a key field in current entity
     * @param string|integer|null $localKey Field name on current entity which matches up with foreign key of foreign entity
     * @param string|integer|null $foreignKey name on foreign entity which matches up with local key of current entity
     */
    public function __construct(EntityInterface $entity, $foreignEntity, $localKey = null, $foreignKey = null)
    {
        //local config
        $this->entity = $entity;
        $this->localKey = $localKey;

        //foreign config
        $this->foreignEntity = $foreignEntity;
        $this->foreignKey = $foreignKey;


    }

    /**
     * Save foreign entity and store value of foreign key into local key field
     *
     * @return EntityInterface
     */
    public function save()
    {
        $foreignEntity = $this->getForeignEntity();
        $entity = $this->getEntity();
        $entity->__set($this->getLocalKey(), $foreignEntity->__get($this->getForeignKey()));

        //save foreign only if it has updates
        if($this->isForeignEntityUpdate()){
            $foreignEntity->getMapper()->save($foreignEntity);
        }

        return $foreignEntity;
    }

    /**
     * Fetch data from foreign entity, when value of foreign key matches up with value of local key
     *
     * @return EntityInterface|\Blast\Db\Entity\EntityInterface[]
     */
    public function fetch()
    {
        return $this->getEntity()->getMapper()->findBy($this->getLocalKey(), $this->getForeignEntity()->__get($this->getLocalKey()));
    }

}