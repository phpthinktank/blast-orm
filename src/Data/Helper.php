<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 12.02.2016
 * Time: 10:02
 *
 */

namespace Blast\Db\Data;

class Helper
{

    /**
     * receive data from object
     *
     * @param $object
     * @return array
     */
    public static function receiveDataFromObject($object)
    {
        $data = [];
        if ($object instanceof DataObjectInterface) {
            $data = $object->getData();
        } elseif ($object instanceof \ArrayObject) {
            $data = $object->getArrayCopy();
        } elseif (is_object($object)) {
            $data = (array) $object;
        }
        return $data;
    }

    /**
     * receive data from object
     *
     * @param $object
     * @param array $data
     * @return array
     */
    public static function replaceDataFromObject($object, $data = [])
    {
        if ($object instanceof DataObjectInterface) {
            $object->setData($data);
        } elseif ($object instanceof \ArrayObject) {
            $object->exchangeArray($data);
        } elseif (is_object($object)) {
            foreach($data as $key => $value){
                $object->$key = $value;
            }
        }
    }

}