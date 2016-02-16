<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 16.02.2016
 * Time: 14:11
 *
 */

namespace Blast\Db\Orm;


use Blast\Db\Orm\Model\ModelManager;

class Factory
{

    public static function createMapper($name)
    {
        $manager = ModelManager::getInstance();
        $model = $manager->getModel($name);
        $mapper = $manager->getMapper($name);

        return $mapper;

    }

}