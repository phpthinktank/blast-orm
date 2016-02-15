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

namespace Blast\Db\Orm\Relations;


use Blast\Db\Entity\EntityInterface;
use Blast\Db\Query;

class ManyThroughMany extends AbstractRelation
{

    /**
     * @var EntityInterface
     */
    protected $throughEntity;

    /**
     * @param EntityInterface $model current entity instance
     * @param string|EntityInterface $foreignEntity Entity which have a key field in current entity
     * @param EntityInterface $throughEntity
     * @param string|integer|null $localKey Field name on current entity which matches up with foreign key of foreign entity
     * @param string|integer|null $foreignKey name on foreign entity which matches up with local key of current entity
     */
    public function __construct(EntityInterface $model, EntityInterface $foreignEntity, EntityInterface $throughEntity, $localKey = null, $foreignKey = null)
    {
        //local config
        $this->entity = $model;
        $this->localKey = $localKey;

        //foreign config
        $this->foreignEntity = $foreignEntity;
        $this->foreignKey = $foreignKey;

        $this->throughEntity = $throughEntity;
    }

    /**
     * @return EntityInterface
     */
    public function getThroughEntity()
    {
        return $this->throughEntity;
    }

    /**
     * Save related entities
     * @return mixed
     * @throws \Exception
     */
    public function save()
    {
        //@todo Add saving for many through many relations
        throw new \Exception('Not implemented yet');
    }

    /**
     * Fetch related entities
     *
     * @return mixed
     */
    public function fetch()
    {
        //@todo maybe we want to use a join instead of huge double query
        $query = $this->getThroughEntity()->getMapper()->select();
        $through = $query->select($query->expr()->eq(
            $this->getLocalKey() . '_id',
            $this->getForeignEntity()->get($this->getForeignEntity()->getTable()->getPrimaryKeyName())
        ))->execute(Query::RESULT_COLLECTION);

        if (!is_array($through) && $through == $this->getThroughEntity()) {
            $through = [$through];
        }

        if (!is_array($through)) {
            return false;
        }

        $query = $this->getForeignEntity()->getMapper()->select();

        foreach ($through as $model) {
            $query->orWhere($query->expr()->eq($this->getForeignKey(), $model->get($this->getForeignKey() . '_id')));
        }

        $result = $query->execute(Query::RESULT_COLLECTION);
        return $this->setResults($result)->getResults();
    }
}