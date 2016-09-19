<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 17.09.2016
 * Time: 10:14
 */

namespace Blast\Orm\Query;


class Result implements ResultInterface
{
    /**
     * @var \ArrayObject
     */
    private $data;

    /**
     * create a new result
     *
     * @param $data
     */
    public function __construct($data)
    {

        $this->data = new \ArrayObject((array)$data);
    }

    /**
     * Get field value
     *
     * @param $field
     * @return mixed|bool
     */
    public function get($field)
    {
        return $this->has($field) ? $this->data->offsetGet($field) : false;
    }

    /**
     * Check if field exists
     *
     * @param $field
     * @return mixed
     */
    public function has($field)
    {
        return $this->data->offsetExists($field);
    }

    /**
     * Get array copy of result
     *
     * @return array
     */
    public function toArray()
    {
        return $this->data->getArrayCopy();
    }
}
