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

class ManyThroughMany extends AbstractRelation
{

    /**
     * @var EntityInterface
     */
    protected $throughEntity;

    /**
     * @param EntityInterface $entity current entity instance
     * @param string|EntityInterface $foreignEntity Entity which have a key field in current entity
     * @param EntityInterface $throughEntity
     * @param string|integer|null $localKey Field name on current entity which matches up with foreign key of foreign entity
     * @param string|integer|null $foreignKey name on foreign entity which matches up with local key of current entity
     */
    public function __construct(EntityInterface $entity, EntityInterface $foreignEntity, EntityInterface $throughEntity, $localKey = null, $foreignKey = null)
    {
        //local config
        $this->entity = $entity;
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
        throw new \Exception('No implemented yet');
    }

    /**
     * Fetch related entities
     *
     * @return mixed
     */
    public function fetch()
    {
        $through = $this->getThroughEntity()->getMapper()
            ->findBy(
                $this->getLocalKey() . '_id',
                $this->getForeignEntity()->get(
                    $this->getForeignEntity()->getTable()->getPrimaryKeyName()
                )
            );

        if(!is_array($through) && $through == $this->getThroughEntity()){
            $through = [$through];
        }

        if(!is_array($through)){
            return false;
        }

        $mapper = $this->getForeignEntity()->getMapper();
        $builder = $mapper->getQueryBuilder();

        foreach($through as $entity){
            $builder->orWhere($builder->expr()->eq($this->getForeignKey(), $entity->get($this->getForeignKey() . '_id')));
        }

        return $mapper->fetch($builder);
    }
}