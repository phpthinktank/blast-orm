<?php
/*
*
* (c) Marco Bunge <marco_bunge@web.de>
*
* For the full copyright and license information, please view the LICENSE.txt
* file that was distributed with this source code.
*
* Date: 18.02.2016
* Time: 20:50
*/

namespace Blast\Db\Orm\Model;


trait ModelAwareTrait
{
    /**
     * @var ModelInterface
     */
    private $model;

    /**
     * Create mapper for Model
     * @param ModelInterface
     * @return $this
     */
    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @return ModelInterface
     */
    public function getModel()
    {
        return $this->model;
    }
}