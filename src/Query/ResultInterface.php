<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 17.09.2016
 * Time: 10:20
 */

namespace Blast\Orm\Query;


interface ResultInterface
{

    /**
     * create a new result
     *
     * @param $data
     */
    public function __construct($data);

    /**
     * Get field value
     *
     * @param $field
     * @return mixed
     */
    public function get($field);

    /**
     * Check if field exists
     *
     * @param $field
     * @return mixed
     */
    public function has($field);

    /**
     * Get array copy of result
     *
     * @return array
     */
    public function toArray();

}
