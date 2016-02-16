<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 16.02.2016
 * Time: 16:52
 *
 */

namespace Blast\Db\Orm\Model;


interface ModelAwareInterface
{
    /**
     * @param $model
     * @return $this
     */
    public function setModel($model);

    /**
     * @return ModelInterface
     */
    public function getModel();
}