<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 25.11.2015
 * Time: 17:48
 */

namespace Blast\Db\Entity;

use Blast\Db\Events\ValueEvent;
use Blast\Db\Orm\Factory;
use Blast\Db\Orm\Mapper;
use Blast\Db\Orm\MapperInterface;
use Blast\Db\Relations\AbstractRelation;
use Doctrine\DBAL\Schema\Table;
use League\Event\Emitter;
use League\Event\EmitterInterface;

abstract class AbstractEntity implements EntityInterface
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var MapperInterface
     */
    protected $mapper;

    /**
     * @var bool
     */
    protected $new = true;

    /**
     * @var string
     */
    protected $table = null;

    /**
     * @var Emitter
     */
    protected $emitter;

    /**
     * @var AbstractRelation[]
     */
    protected $relations;

    /**
     * @var bool
     */
    protected $updated = false;

    /**
     * @var array
     */
    protected $originalData = [];

    /**
     * AbstractEntity constructor.
     */
    public function __construct()
    {
        $this->configure();
        $this->attachDefaultValues();
    }

    /**
     * @return $this
     */
    protected function attachDefaultValues()
    {
        $fields = $this->getTable()->getColumns();
        foreach ($fields as $name => $field) {
            $this->__set($name, $field['default']);
        }

        //reset updates, default values should not be triggered as updates
        $this->setUpdated(false);
        $this->originalData = [];

        return $this;
    }

    /**
     * @return boolean
     */
    public function isUpdated()
    {
        return $this->updated;
    }

    /**
     * @param boolean $updated
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    }

    /**
     * @return array
     */
    public function getOriginalData()
    {
        return $this->originalData;
    }

    public function getUpdatedData()
    {
        $original = $this->getOriginalData();
        $updated = [];

        foreach($original as $key => $value){
            if($value !== $this->get($key)){
                $updated[$key] = $value;
            }
        }

        return $updated;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $data = [];
        foreach(array_keys($this->data) as $key){
            $data[$key] = $this->get($key);
        }
        return $data;
    }

    /**
     * @param array $data
     * @return AbstractEntity
     */
    public function setData(array $data)
    {
        foreach ($data as $name => $value) {
            $this->set($name, $data);
        }

        return $this;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function get($name)
    {
        if ($this->__isset($name)) {
            $value = $this->data[$name];
        } elseif ($this->hasRelation($name)) {
            $value = $this->getRelation($name)->fetch();
        } else {
            $value = null;
        }
        return $this->emitValueEvent(static::VALUE_GET, $name, $value)->getValue();
    }

    /**
     * @param $name
     * @param $value
     * @return mixed
     */
    public function set($name, $value)
    {
        if(!$this->isUpdated()){
            $this->setUpdated(true);
            $this->originalData = $this->getData();
        }

        if ($this->getTable()->hasColumn($name)) {
            $this->data[$name] = $this->emitValueEvent(static::VALUE_SET, $name, $value)->getValue();
        }elseif($this->hasRelation($name)){
            if(!($value instanceof EntityInterface)){
                throw new \InvalidArgumentException('Unable to update Relation. Given entity needs to be an instance of ' . EntityInterface::class);
            }

            $relation = $this->getRelation($name);
            $relation->setForeignEntity($value);

            //reattach relation
            //this is probably unnecessary!
            $this->relations[$name] = $relation;
        }
        return $this;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
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
        if ($this->__isset($name)) {
            $this->data[$name] = null;
        }
    }

    public function __call($name, $arguments)
    {
        $isAccessor = strpos(strtolower($name), 'get') === 0;
        $isMutator = strpos(strtolower($name), 'set') === 0;


        //call accessor or mutator
        if (($isAccessor || $isMutator) && !$this->hasRelation($name)) {

            $method = $isMutator ? 'set' : 'get';
            $args = [lcfirst(str_replace('set', '', $name))];

            if ($isMutator) {
                $args[] = array_shift($arguments);
            }

            return call_user_func_array([$this, $method], $args);

        } elseif ($this->hasRelation($name)) {
            //call relation without fetching it
            return $this->getRelation($name);
        }

        throw new \BadMethodCallException('Unknown method ' . __METHOD__);
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
     * @return Table
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param Table $table
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * @return Emitter
     */
    public function getEmitter()
    {
        if ($this->emitter === null) {
            $this->emitter = Factory::getInstance()->getContainer()->get(EmitterInterface::class);
        }
        return $this->emitter;
    }

    /**
     * @return MapperInterface
     */
    public function getMapper()
    {
        return Factory::getInstance()->createMapper($this->mapper);
    }

    /**
     * empty data
     */
    public function reset()
    {
        $this->data = [];
    }

    /**
     * Configure entity
     */
    abstract public function configure();

    /**
     * @return AbstractRelation[]
     */
    public function getRelations()
    {
        return $this->relations;
    }

    public function addRelation(AbstractRelation $relation, $name = null)
    {
        if ($name === null) {
            $name = $relation->getForeignEntity()->getTable()->getName() . '.' . $relation->getForeignKey();
        }

        if ($this->hasRelation($name)) {
            throw new \InvalidArgumentException(sprintf('Relation %s already exists', $name));
        }

        $this->relations[$name] = $relation;
    }

    /**
     * @param $name
     * @return AbstractRelation
     */
    public function getRelation($name)
    {
        return $this->hasRelation($name) ? $this->relations[$name] : null;
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasRelation($name)
    {
        return isset($this->relations[$name]);
    }

    /**
     * @param $eventName
     * @param $key
     * @param $value
     * @return ValueEvent|\League\Event\EventInterface
     */
    private function emitValueEvent($eventName, $key, $value)
    {
        return $this->getEmitter()->emit(new ValueEvent($eventName, $key, $value));
    }

    /**
     * save current entity
     * @return int
     */
    public function save()
    {
        return $this->getMapper()->save($this);
    }

    /**
     * delete current entity
     * @return int
     */
    public function delete()
    {
        return $this->getMapper()->delete($this);
    }

    /**
     * result by primary key
     * @param $pk
     * @return mixed
     */
    public static function find($pk)
    {
        return (new static)->getMapper()->find($pk);
    }

    /**
     * find result by field
     *
     * @param $field
     * @param $value
     * @return mixed
     */
    public static function findBy($field, $value)
    {
        return (new static)->getMapper()->findBy($field, $value);
    }


}