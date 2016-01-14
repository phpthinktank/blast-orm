<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 25.11.2015
 * Time: 17:48
 */

namespace Blast\Orm\Entity;


abstract class AbstractEntity extends \ArrayObject implements EntityInterface
{

    /**
     * @var null
     */
    protected $table = null;

    /**
     * @var bool
     */
    private $new = true;

    /**
     * @var null
     */
    protected $primaryKeyField = null;

    /**
     * @var null
     */
    protected $primaryKey = null;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @return null
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param null $table
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * @return boolean
     */
    public function isNew()
    {
        return $this->new;
    }

    /**
     * @param boolean $new
     */
    public function setNew($new)
    {
        $this->new = $new;
    }

    /**
     * @return null
     */
    public function primaryKeyField()
    {
        return $this->primaryKeyField;
    }

    /**
     *
     */
    public function primaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return AbstractEntity
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->data[$name];
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * @param $name
     */
    public function __unset($name)
    {
        unset($this->data[$name]);
    }


}