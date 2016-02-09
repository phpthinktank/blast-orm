<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 25.11.2015
 * Time: 17:40
 */

namespace Blast\Db\Entity;

use Blast\Db\Orm\MapperAwareInterface;
use Blast\Db\Relations\AbstractRelation;
use Blast\Db\Schema\Table;
use League\Event\EmitterInterface;

interface EntityInterface extends MapperAwareInterface
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
     * @return ManagerInterface
     */
    public function getManager();

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
     * @return boolean
     */
    public function isUpdated();

    /**
     * @return array
     */
    public function getOriginalData();

    /**
     * @return array
     */
    public function getUpdatedData();

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
     * Call accessors, mutators or relations
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments);

    /**
     * @param $name
     * @return mixed
     */
    public function get($name);

    /**
     * @param $name
     * @param $value
     * @return mixed
     */
    public function set($name, $value);

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
     * @return AbstractRelation[]
     */
    public function getRelations();


}