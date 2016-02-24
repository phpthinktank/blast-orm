<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 24.02.2016
 * Time: 10:41
 *
 */

namespace Blast\Orm\Data;


use Blast\Orm\Object\ObjectAdapter;

class DataAdapter extends ObjectAdapter implements DataObjectInterface
{
    const DATA_DEFAULT_VALUE = '_____DATA_DEFAULT_VALUE_____';

    /**
     * Receive data
     * @return array
     */
    public function getData()
    {

        $data = [];
        $source = $this->getObject();
        $reflection = new \ReflectionObject($source);
        if ($reflection->hasMethod('getData')) {
            $data = $source->getData();
        } elseif ($reflection->hasMethod('data')) {
            $data = $source->data();
        } elseif ($reflection->hasMethod('getArrayCopy')) {
            $data = $source->getArrayCopy();
        } elseif (is_object($source)) {
            $properties = $reflection->getProperties();
            foreach ($properties as $property) {
                $value = $this->access(
                    $property->getName(),
                    static::DATA_DEFAULT_VALUE,
                    $source,
                    \ReflectionMethod::IS_PRIVATE | \ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_PROTECTED | \ReflectionMethod::IS_FINAL, static::IS_PROPERTY);

                if ($value === static::DATA_DEFAULT_VALUE) {
                    $data[$property->getName()] = $value;
                }
            }

        }

        return $data;
    }

    /**
     * Replace data
     * @param array $data
     * @return $this
     */
    public function setData($data = [])
    {
        $source = $this->getObject();
        $reflection = new \ReflectionObject($source);
        if ($reflection->hasMethod('setData')) {
            $source->setData($data);
        } elseif ($reflection->hasMethod('data') && count($reflection->getMethod('data')->getParameters()) > 0) {
            $source->data($data);
        } elseif ($reflection->hasMethod('exchangeArray')) {
            $source->exchangeArray($data);
        } elseif (is_object($source)) {
            $properties = $reflection->getProperties();
            foreach ($properties as $property) {
                if (!isset($data[$property->getName()])) {
                    continue;
                }
                $this->mutate(
                    $property->getName(),
                    $data[$property->getName()],
                    $source,
                    \ReflectionMethod::IS_PRIVATE | \ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_PROTECTED | \ReflectionMethod::IS_FINAL, static::IS_PROPERTY);
            }

        }

        return $this;
    }
}