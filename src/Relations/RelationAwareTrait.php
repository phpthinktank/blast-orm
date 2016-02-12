<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 09.02.2016
 * Time: 11:24
 *
 */

namespace Blast\Db\Relations;

trait RelationAwareTrait
{
    /**
     * @var AbstractRelation[]
     */
    protected $relations = [];

    /**
     * @return AbstractRelation[]
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * add a new relation for entity
     *
     * @param AbstractRelation $relation
     * @param null $name
     * @return $this
     */
    public function addRelation(AbstractRelation $relation, $name = null)
    {
        if ($name === null) {
            $name = $relation->getForeignEntity()->getTable()->getName();
        }

        if ($this->hasRelation($name)) {
            throw new \InvalidArgumentException(sprintf('Relation %s already exists', $name));
        }

        $this->relations[$name] = $relation;
        return $this;
    }

    /**
     * @param $name
     * @return AbstractRelation
     */
    public function getRelation($name)
    {
        return $this->hasRelation($name) ? $this->relations[$name] : null;
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasRelation($name)
    {
        return isset($this->relations[$name]);
    }

}