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
use Blast\Db\Query;

class HasOne extends AbstractRelation
{
    /**
     * BelongsTo constructor.
     * @param EntityInterface $model current entity instance
     * @param string|EntityInterface $foreignEntity Entity which have a key field in current entity
     * @param string|integer|null $localKey Field name on current entity which matches up with foreign key of foreign entity
     * @param string|integer|null $foreignKey name on foreign entity which matches up with local key of current entity
     */
    public function __construct(EntityInterface $model, $foreignEntity, $localKey = null, $foreignKey = null)
    {
        //local config
        $this->entity = $model;
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
        $model = $this->getEntity();
        $model->__set($this->getLocalKey(), $foreignEntity->__get($this->getForeignKey()));
        $foreignEntity->getMapper()->save($this->getResults());

        return $foreignEntity;
    }

    /**
     * Fetch data from foreign entity, when value of foreign key matches up with value of local key
     *
     * @return EntityInterface|\Blast\Db\Entity\EntityInterface[]
     */
    public function fetch()
    {
        $query = $this->getForeignEntity()->getMapper()->select();
        $result = $query->where(
            $query->expr()->eq($this->getLocalKey(), $this->getForeignEntity()->get($this->getLocalKey()))
        )->setMaxResults(1)->execute(Query::RESULT_ENTITY);
        return $this->setResults($result)->getResults();
    }

}