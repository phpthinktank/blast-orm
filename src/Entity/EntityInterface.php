<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 25.11.2015
 * Time: 17:40
 */

namespace Blast\Db\Entity;


use Blast\Db\Orm\MapperInterface;
use Blast\Db\Schema\Table;
use League\Event\EmitterInterface;

interface EntityInterface
{

    const BEFORE_SAVE = 'save.before';
    const AFTER_SAVE = 'save.before';
    const BEFORE_CREATE = 'create.before';
    const AFTER_CREATE = 'create.before';
    const BEFORE_UPDATE = 'update.before';
    const AFTER_UPDATE = 'update.before';
    const BEFORE_DELETE = 'delete.before';
    const AFTER_DELETE = 'delete.before';
    const VALUE_GET = 'value.get';
    const VALUE_SET = 'value.set';

    /**
     *
     */
    public function __construct();

    /**
     * @return Table
     */
    public function getTable();

    /**
     * @return boolean
     */
    public function isNew();

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data);

    /**
     * @return array
     */
    public function getData();

    /**
     * @return MapperInterface
     */
    public function getMapper();

    /**
     * @return EmitterInterface
     */
    public function getEmitter();

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name);

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value);

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name);

    /**
     * @param $name
     */
    public function __unset($name);

    /**
     * Flush all data
     * @return mixed
     */
    public function reset();

    /**
     *
     */
    public function configure();

    /**
     * @param $pk
     * @return mixed
     */
    public static function find($pk);

    /**
     * @param $field
     * @param $value
     * @return mixed
     */
    public static function findBy($field, $value);

    /**
     * Save current entity
     * @return mixed
     */
    public function save();

    /**
     * Delete current entity
     * @return mixed
     */
    public function delete();


}