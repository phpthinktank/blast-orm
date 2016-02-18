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

namespace Blast\Db\Orm\Relations;


abstract class AbstractRelation implements RelationInterface
{

    use RelationTrait;

    /**
     * Save related entities
     *
     * @return mixed
     */
    abstract public function save();

    /**
     * Fetch related entities
     *
     * @return mixed
     */
    abstract public function fetch();

}