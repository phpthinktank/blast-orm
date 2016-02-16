<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 16.02.2016
 * Time: 13:32
 *
 */

namespace Blast\Db\Orm\Model;


use Blast\Db\Orm\MapperInterface;

interface ModelFieldsAwareInterface
{

    /**
     * Attach fields to model
     *
     * @param MapperInterface $mapper
     * @param ModelInterface $model
     * @return mixed
     */
    public static function attachFields(MapperInterface $mapper, ModelInterface $model);

}