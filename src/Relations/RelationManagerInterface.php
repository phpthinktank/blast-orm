<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 09.02.2016
 * Time: 11:29
 *
 */

namespace Blast\Db\Relations;


interface RelationManagerInterface
{
    /**
     * @return AbstractRelation[]
     */
    public function getRelations();

    /**
     * add a new relation for entity
     *
     * @param AbstractRelation $relation
     * @param null $name
     * @return $this
     */
    public function addRelation(AbstractRelation $relation, $name = null);

    /**
     * @param $name
     * @return AbstractRelation
     */
    public function getRelation($name);

    /**
     * @param $name
     * @return bool
     */
    public function hasRelation($name);
}