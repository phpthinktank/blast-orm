<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 29.02.2016
 * Time: 12:07
 *
 */

namespace Blast\Orm\Relations;

use Blast\Orm\Entity\EntityAwareInterface;
use Blast\Orm\Query;

interface RelationInterface extends EntityAwareInterface
{
    /**
     * @return Query
     */
    public function getQuery();

    /**
     * Name of relation
     *
     * @return string
     */
    public function getName();

    /**
     * @return array|\ArrayObject||bool
     */
    public function execute();
}
