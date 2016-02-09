<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 02.02.2016
 * Time: 15:56
 *
 */

namespace Blast\Db\Relations;


use Blast\Db\Entity\EntityInterface;

class HasMany extends AbstractRelation
{

    /**
     * @var EntityInterface[]
     */
    protected $foreignEntities = [];

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
     * @return \Blast\Db\Entity\EntityInterface[]
     */
    public function getForeignEntities()
    {
        return $this->foreignEntities;
    }

    /**
     * @param \Blast\Db\Entity\EntityInterface[] $foreignEntities
     */
    public function setForeignEntities($foreignEntities)
    {
        $this->foreignEntities = $foreignEntities;
    }

    /**
     * @todo Entity should be able to save many entities
     * Save foreign entity and store value of foreign key into local key field
     * @return EntityInterface
     */
    public function save()
    {
        $foreignEntity = $this->getForeignEntity();
        $entity = $this->getEntity();
        $entity->__set($this->getLocalKey(), $foreignEntity->__get($this->getForeignKey()));

        //save foreign only if it has updates
        if($foreignEntity->isUpdated()){
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