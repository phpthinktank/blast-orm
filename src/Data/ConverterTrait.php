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

namespace Blast\Orm\Data;


trait ConverterTrait
{
    /**
     * Convert data to array
     * @return array
     */
    public function toArray(){
        $data = DataHelper::receiveDataFromObject($this);

        if(is_object($data)){ //convert object to array
            // @codeCoverageIgnoreStart
            $data = (array) $data;
            // @codeCoverageIgnoreEnd
        }elseif(is_scalar($data)){ //convert scalar to array
            // @codeCoverageIgnoreStart
            $data = [$data];
            // @codeCoverageIgnoreEnd
        }

        if(!is_array($data)){
            // @codeCoverageIgnoreStart
            throw new \InvalidArgumentException('Unable to convert data to an array!');
            // @codeCoverageIgnoreEnd
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