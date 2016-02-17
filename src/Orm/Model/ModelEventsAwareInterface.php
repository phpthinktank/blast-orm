<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 17.02.2016
 * Time: 11:21
 *
 */

namespace Blast\Db\Orm\Model;


use Blast\Db\Orm\MapperInterface;

interface ModelEventsAwareInterface
{
    /**
     * Attach events to model
     *
     * @param MapperInterface $mapper
     * @param ModelInterface $model
     * @return mixed
     */
    public static function attachFields(MapperInterface $mapper, ModelInterface $model);
}