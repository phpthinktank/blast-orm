<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 16.02.2016
 * Time: 11:21
 *
 */

namespace Blast\Db\Orm\Model;


trait ModelTrait
{
    /**
     * @var bool
     */
    private $new = false;

    /**
     * @var array
     */
    private $updatedData = [];

    /**
     * @var array
     */
    private $originalData = [];

    /**
     * Check if entry is new or already exists
     *
     * @return boolean
     */
    public function isNew()
    {
        return $this->new;
    }

    public function setNew($new = true){
        $this->new = $new;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isUpdated()
    {
        return count($this->getUpdatedData()) > 0;
    }

    /**
     * @return array
     */
    public function getUpdatedData()
    {
        return $this->updatedData;
    }

    /**
     * Hook on beforeDataSet to
     * @param $data
     */
    protected function beforeDataSetUpdateData($data){
        if(!$this->isUpdated()){
            $this->originalData = $data;
        }

        $this->updatedData = array_diff_assoc($this->originalData, $this->updatedData);
    }
}