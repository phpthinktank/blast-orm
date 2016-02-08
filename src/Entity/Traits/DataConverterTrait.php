<?php
/*
*
* (c) Marco Bunge <marco_bunge@web.de>
*
* For the full copyright and license information, please view the LICENSE.txt
* file that was distributed with this source code.
*
* Date: 08.02.2016
* Time: 15:17
*/

namespace Blast\Db\Entity\Traits;


trait DataConverterTrait
{

    /**
     * Overwrite data for array accessor
     * @return bool
     */
    public function getArrayData(){
        return false;
    }

    /**
     * @return array
     */
    public function toArray(){
        $data = method_exists($this, 'getData') ? $this->getData() : $this->getArrayData();
        if(is_object($data)){
            $data = (array) $data;
        }elseif(is_scalar($data)){
            $data = [$data];
        }

        if(!is_array($data)){
            throw new \InvalidArgumentException('Unable to convert data to an array!');
        }

        return $data;
    }

    /**
     * Convert data to string
     *
     * @return string
     */
    public function toJson(){
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_BIGINT_AS_STRING|JSON_NUMERIC_CHECK);
    }



}