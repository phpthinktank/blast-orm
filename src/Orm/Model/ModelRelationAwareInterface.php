<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 16.02.2016
 * Time: 13:25
 *
 */

namespace Blast\Db\Orm\Model;


use Blast\Db\Orm\MapperInterface;

interface ModelRelationAwareInterface
{
    /**
     * Attach relations from model to mapper
     *
     * @param MapperInterface $mapper
     * @param ModelInterface $model
     * @return void
     */
    public static function attachRelations(MapperInterface $mapper, ModelInterface $model);
}