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


use Blast\Db\Entity\CollectionInterface;
use Blast\Db\Entity\EntityInterface;

interface RelationInterface
{
    /**
     * @return EntityInterface
     */
    public function getEntity();

    /**
     * @return EntityInterface
     */
    public function getForeignEntity();

    /**
     * @param EntityInterface $foreignEntity
     */
    public function setForeignEntity($foreignEntity);

    /**
     * @return int|string
     */
    public function getForeignKey();

    /**
     * @return int|string
     */
    public function getLocalKey();

    /**
     * @return CollectionInterface|EntityInterface
     */
    public function getResults();

    /**
     * @param CollectionInterface|EntityInterface $results
     * @return $this
     */
    public function setResults($results);
}