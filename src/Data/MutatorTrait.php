<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 12.02.2016
 * Time: 09:37
 *
 */

namespace Blast\Orm\Data;


use Blast\Orm\Hook;

trait MutatorTrait
{

    /**
     * Add value to data by name
     *
     * @param $name
     * @param $value
     * @return $this
     */
    public function set($name, $value = null)
    {
        $data = DataHelper::receiveDataFromObject($this);

        //hook before receive data
        $before = Hook::trigger('beforeSet', $this, ['name' => $name, 'data' => $data]);

        //passing data from before hook
        $name = isset($before['name']) ? $before['name'] : $name;
        $data = isset($before['data']) ? $before['data'] : $data;

        //set value
        $data[$name] = $value;

        //hook after receive data
        $after = Hook::trigger('afterSet', $this, ['name' => $name, 'data' => $data]);

        //replace data of object
        $data = isset($after['data']) ? $after['data'] : $data;
        DataHelper::replaceDataFromObject($this, $data);

        return $this;
    }

    /**
     * Remove value to data by name
     *
     * @param $name
     * @return $this
     */
    public function remove($name)
    {
        $data = DataHelper::receiveDataFromObject($this);

        //hook before receive data
        $before = Hook::trigger('beforeRemove', $this, ['name' => $name, 'data' => $data]);

        //passing data from before hook
        $name = isset($before['name']) ? $before['name'] : $name;
        $data = isset($before['data']) ? $before['data'] : $data;

        //remove value
        if(isset($data[$name])){
            unset($data[$name]);
        }

        //hook after receive data
        $after = Hook::trigger('afterRemove', $this, ['name' => $name, 'data' => $data]);

        //replace data of object
        $data = isset($after['data']) ? $after['data'] : $data;

        DataHelper::replaceDataFromObject($this, $data);

        return $this;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }

    /**
     * @param $name
     */
    public function __unset($name)
    {
        $this->remove($name);
    }

}