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

namespace Blast\Db\Data;


trait ConverterTrait
{
    /**
     * Convert data to array
     * @return array
     */
    public function toArray(){
        $data = Helper::receiveDataFromObject($this);

        if(is_object($data)){ //convert object to array
            $data = (array) $data;
        }elseif(is_scalar($data)){ //convert scalar to array
            $data = [$data];
        }

        if(!is_array($data)){
            throw new \InvalidArgumentException('Unable to convert data to an array!');
        }

        return $data;
    }

    /**
     * Convert data to json
     *
     * @param int $options
     * @return string
     */
    public function toJson($options = 0){
        if($options === 0){
            $options = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_BIGINT_AS_STRING | JSON_NUMERIC_CHECK;
        }
        return json_encode($this->toArray(), $options);
    }



}