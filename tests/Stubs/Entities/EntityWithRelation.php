<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 03.03.2016
 * Time: 13:17
 *
 */

namespace Blast\Tests\Orm\Stubs\Entities;


use Blast\Orm\Data\DataObject;
use Blast\Orm\Mapper;
use Blast\Orm\Relations\HasOne;

/**
 * @codeCoverageIgnore
 */
class EntityWithRelation extends \ArrayObject
{
    /**
     * Get table for model
     *
     * @return string
     */
    public static function table()
    {
        return 'testTable';
    }

    public static function relations(EntityWithRelation $entity, Mapper $mapper){
        return [
            new HasOne($mapper->getLocator(), $entity, 'otherTable')
        ];
    }
}
