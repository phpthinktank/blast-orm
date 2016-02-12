<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 11.02.2016
 * Time: 11:34
 *
 */

namespace Blast\Db\Entity;


use Blast\Db\Data\Helper;
use Blast\Db\Hook;

/**
 * Accessing values from data
 *
 * @package Blast\Db\Entity
 */
trait AccessorTrait
{
    /**
     * Get value by name, if no value exists use default value instead
     *
     * @param $name
     * @param null $default
     * @return $this
     */
    public function get($name, $default = null)
    {
        $data = Helper::receiveDataFromObject($this);

        //hook before receive data
        $before = Hook::trigger('beforeGet', $this, ['name' => $name, 'data' => $data]);

        //passing data from before hook
        $name = isset($before['name']) ? $before['name'] : $name;
        $data = isset($before['data']) ? $before['data'] : $data;

        //set value
        $value = $this->has($name) ? $data[$name] : $default;

        //hook after receive data
        $after = Hook::trigger('afterGet', $this, ['name' => $name, 'value' => $value]);

        //determine and return result
        return isset($after['value']) ? $after['value'] : $default;
    }

    /**
     * Check if value exists
     *
     * @param $name
     * @return $this
     */
    public function has($name)
    {
        $data = Helper::receiveDataFromObject($this);

        //hooking before check data
        $before = Hook::trigger('beforeHas', $this, ['name' => $name, 'data' => $data]);

        //passing data from hook
        $name = isset($before['name']) ? $before['name'] : $name;
        $data = isset($before['data']) ? $before['data'] : $data;

        //check if value exists in data
        $value = isset($data[$name]);

        //hooking after check data
        $after = Hook::trigger('afterHas', $this, ['name' => $name, 'value' => $value]);

        //passing result
        return isset($after['value']) ? is_bool($after['value']) ? $after['value'] : false : false;
    }

    /**
     * Access value in data as property
     *
     * @see AccessorTrait::get
     *
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Check if value exists in data with isset()
     *
     * @see AccessorTrait::has
     *
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->has($name);
    }

}