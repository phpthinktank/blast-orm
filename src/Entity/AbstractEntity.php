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

    protected $table = null;

    private $new = true;

    protected $primaryKeyField = null;

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

    public function primaryKeyField()
    {
        return $this->primaryKeyField;
    }

    public function primaryKey()
    {
        // TODO: Implement primaryKey() method.
    }


}