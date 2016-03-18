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
use Doctrine\Common\Inflector\Inflector;

/**
 * @codeCoverageIgnore
 */
class PostWithUserRelation extends \ArrayObject
{
    /**
     * Get table for model
     *
     * @return string
     */
    public static function tableName()
    {
        return 'post';
    }

    public static function relations(PostWithUserRelation $entity, Mapper $mapper){
        return [
            $mapper->belongsTo($entity, User::class)
        ];
    }
}
