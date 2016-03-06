<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 24.02.2016
 * Time: 09:55
 *
 */

namespace Blast\Orm\Entity;


use Blast\Orm\Data\DataAdapter;
use Blast\Orm\Data\DataObject;
use Blast\Orm\Data\DataObjectInterface;
use Blast\Orm\Mapper;
use Blast\Orm\MapperInterface;
use Blast\Orm\Query;
use Blast\Orm\Relations\RelationInterface;
use Doctrine\DBAL\Driver\Statement;
use League\Event\EmitterAwareTrait;

class EntityAdapter implements EntityAdapterInterface, DataObjectInterface
{

    use EmitterAwareTrait;

    const DATA_DEFAULT_VALUE = '_____DATA_DEFAULT_VALUE_____';

    /**
     * @var Query
     */
    private $query;

    /**
     * @var MapperInterface
     */
    private $mapper;

    /**
     * @var object
     */
    private $object = null;

    /**
     * @var \ReflectionObject
     */
    private $reflection = null;

    /**
     * EntityAdapter constructor.
     * @param array|\stdClass|\ArrayObject|object|string $object
     */
    public function __construct($object = null)
    {
        $this->setObject(EntityAdapterCollectionFacade::createObject($object));
    }

    /**
     * Get entity class name
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->getReflection()->getName();
    }

    /**
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param string|object $class
     * @return $this
     */
    public function setObject($class)
    {
        if (is_string($class)) {
            $class = (new \ReflectionClass($class))->newInstance();
        }

        $this->object = $class;

        return $this;
    }

    /**
     * @return \ReflectionObject
     */
    public function getReflection()
    {
        if ($this->reflection === null) {
            $this->reflection = new \ReflectionObject($this->getObject());
        }

        return $this->reflection;
    }

    /**
     * Get table name from entity. If no table name is declared determine from class name and convert camelcase to
     * underscore
     *
     * @return string
     */
    public function getTableName()
    {
        $default = $this->camelCaseToUnderScore($this->getReflection()->getShortName());

        return $this->getDefinition('tableName', $default);
    }

    /**
     * Get entity primary key name
     *
     * @return string
     */
    public function getPrimaryKeyName()
    {
        return $this->getDefinition('primaryKeyName', static::DEFAULT_PRIMARY_KEY_NAME);
    }

    /**
     * Get entity fields
     *
     * @return \Doctrine\DBAL\Schema\Column[]
     *
     * Currently not supported
     * @codeCoverageIgnore
     */
    public function getFields()
    {
        return $this->getDefinition('fields', []);
    }

    /**
     * Get entity indexes
     *
     * @return \Doctrine\DBAL\Schema\Index[]
     *
     * Currently not supported
     * @codeCoverageIgnore
     */
    public function getIndexes()
    {
        return $this->getDefinition('index', []);
    }

    /**
     * Return an array of relations
     * @return RelationInterface[]
     */
    public function getRelations()
    {
//        $reflection = $this->getReflection();
//        $default = [];
//        if (!$reflection->hasMethod('relations')) {
//            return $default;
//        }
//
//        $method = $reflection->getMethod('relations');
//
//        if (!$method->isStatic()) {
//            return $default;
//        }
//
//        $relations = $method->invokeArgs($this->getObject(), [$this->getObject()]);
        $relations = $this->callDefinition('relations', [], [$this->getObject()], 'get');

        $result = [];
        foreach ($relations as $name => $relation) {
            if (!($relation instanceof RelationInterface)) {
                continue;
            }
            if (is_numeric($name)) {
                $name = $relation->getName();
            }

            //relations must not overwrite data fields by name
            if (!isset($result[$name])) {
                $result[$name] = $relation;
            }
        }

        return $result;
    }

    /**
     * Hydrate data in entity or collection
     * @param array $data
     * @param string $option
     * @return array|\ArrayObject|DataObject|null|object|\stdClass
     */
    public function hydrate($data = [], $option = self::AUTO)
    {
        if ($this->isRaw($data, $option)) {
            return $data;
        }

        $count = count($data);
        $entity = null;

        if ($option === self::AUTO) {
            $option = $count > 1 || $count === 0 ? self::HYDRATE_COLLECTION : self::HYDRATE_ENTITY;
        }

        if ($option === self::HYDRATE_COLLECTION) { //if entity set has many items, return a collection of entities
            foreach ($data as $key => $value) {
                $data[$key] = $this->map($value);
            }
            $entity = new DataObject();
            $entity->setData($data);
        } elseif ($option === self::HYDRATE_ENTITY) { //if entity has one item, return the entity
            $entity = $this->map(array_shift($data));
        }

        return $entity;
    }

    /**
     * set query object
     * @param Query $query
     * @return mixed|null
     *
     * Currently not supported
     * @codeCoverageIgnore
     */
    public function setQuery(Query $query)
    {
        $this->query = $query;

        return $this->getDefinition('query', $query);
    }

    /**
     * get query object from entity
     *
     * @return mixed|null
     *
     * Currently not supported
     * @codeCoverageIgnore
     */
    public function getQuery()
    {
        return $this->setDefinition('query', $this->query);
    }

    /**
     * @return bool
     */
    public function isNew()
    {
        $entity = $this->getObject();
        if (method_exists($entity, 'isNew')) {
            return $entity->isNew();
        } elseif (property_exists($entity, 'new')) {
            return $entity->new;
        }

        $data = $this->getData();
        $pk = $this->getPrimaryKeyName();
        $isNew = true;

        if (!isset($data[$pk])) {
            $isNew = empty($data[$pk]);
        }

        return $isNew;
    }

    /**
     * Get entity mapper
     *
     * @return MapperInterface
     */
    public function getMapper()
    {
        if ($this->mapper === null) {
            $this->mapper = new Mapper($this->getObject());
        }

        return $this->getDefinition('mapper', $this->mapper);
    }

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

                $value = $this->get($property->getName(), static::DATA_DEFAULT_VALUE);

                if ($value !== static::DATA_DEFAULT_VALUE) {
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

                $this->set($property->getName(), $data[$property->getName()]);
            }

        }

        return $this;
    }

    /**
     * Fetch all data without relations
     */
    public function getDataWithoutRelations()
    {
        $data = $this->getData();
        $relations = $this->getRelations();

        foreach ($data as $key => $value) {
            if (isset($relations[$key])) {
                unset($data[$key]);
            }
        }

        return $data;
    }


    /**
     * Pass data to result or model
     * @param array $data
     * @return array|\stdClass|\ArrayObject|object
     */
    protected function map($data = [])
    {
        //map data
        $this->mapData($data);

        //map relations
        $this->mapRelations();

        $object = $this->getObject();
        $this->reset();

        return $object;
    }

    /**
     * @param $data
     * @param $option
     * @return bool
     */
    protected function isRaw($data, $option)
    {
        return $option === self::HYDRATE_RAW ||
        $data instanceof Statement ||
        is_numeric($data) ||
        is_bool($data);
    }

    /**
     * Map data to source object
     *
     * @param $data
     */
    private function mapData($data)
    {
        $this->setData($data);
    }

    /**
     * Map relations to data. If entry exists with relation name,
     * only overwrite if entry is empty.
     */
    private function mapRelations()
    {
        $relations = $this->getRelations();
        if (count($relations) > 0) {
            $data = $this->getData();
            foreach ($relations as $name => $relation) {

                //avoid overwriting data
                if (!isset($data[$name])) {
                    $data[$name] = $relation;
                }

                //overwrite empty entry with relation
                if ($data[$name] === null) {
                    $data[$name] = $relation;
                }
            }

            $this->setData($data);
        }
    }

    /**
     * Reset object and reflection
     */
    protected function reset()
    {
        $this->resetObject();
        $this->resetReflection();
    }

    /**
     * reset reflection
     */
    protected function resetReflection()
    {
        $this->reflection = null;
    }

    /**
     * reset object
     */
    protected function resetObject()
    {
        $reflection = $this->getReflection();

        $this->setObject(
            $this->getObject() instanceof GenericEntity ?
                $reflection->newInstanceArgs([$this->getTableName()]) :
                $reflection->newInstance()
        );
    }

    /**
     * @param string $string
     * @return string
     */
    private function camelCaseToUnderScore($string)
    {
        return ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $string)), '_');
    }

    /**
     * Call a public method or property from entity.
     *
     * @param $methodOrProperty
     * @param $default
     * @param array $args
     * @param $prefix
     * @param bool $isStatic
     * @return mixed
     */
    public function call($methodOrProperty, $default = null, $args = [], $prefix = null, $isStatic = false)
    {
        $reflection = $this->getReflection();
        $value = $default;
        $prefixed = null === $prefix ? null : $prefix . ucfirst($methodOrProperty);
        if ($reflection->hasMethod($methodOrProperty) || $reflection->hasMethod($prefixed)) {
            $method = $reflection->hasMethod($methodOrProperty) ? $reflection->getMethod($methodOrProperty) : $reflection->getMethod($prefixed);
            $cond = $isStatic ? $method->isStatic() : true;
            $value = $cond && $method->isPublic() ? $method->invokeArgs($this->getObject(),
                $args) : $value;
        } elseif ($reflection->hasProperty($methodOrProperty)) {
            $property = $reflection->getProperty($methodOrProperty);
            $cond = $isStatic ? $property->isStatic() : true;
            if ($cond && $property->isPublic()) {
                $value = $property->getValue($this->getObject());
                if (!empty($args)) {
                    if (is_callable($value)) {
                        $value = call_user_func_array($value, $args);
                    } else {
                        $property->setValue($args);
                        $value = $args;
                    }
                }
            }
        }

        return $value;
    }

    /**
     * Call a definition
     * @param $methodOrProperty
     * @param null $default
     * @param array $args
     * @param null $prefix
     * @return mixed
     */
    public function callDefinition($methodOrProperty, $default = null, $args = [], $prefix = null)
    {
        return $this->call($methodOrProperty, $default, $args, $prefix, true);
    }

    public function getDefinition($methodOrProperty, $default = null)
    {
        return $this->callDefinition($methodOrProperty, $default, [], 'get');
    }

    public function setDefinition($methodOrProperty, $value = null)
    {
        $this->callDefinition($methodOrProperty, null, [$value], 'get');

        return $this;
    }

    /**
     * Get an entity field value
     *
     * @param $field
     * @param null $default
     * @return mixed
     */
    public function get($field, $default = null)
    {

        $value = $this->call($field, static::DATA_DEFAULT_VALUE, [], 'get');

        if (self::DATA_DEFAULT_VALUE !== $value) {
            return $value;
        }

        $value = $this->call('get', static::DATA_DEFAULT_VALUE, [$field]);

        if (self::DATA_DEFAULT_VALUE !== $value) {
            return $value;
        }

        $value = $this->call('data', static::DATA_DEFAULT_VALUE, [], 'get');

        if (!is_array($value)) {
            return $default;
        }

        if (isset($value[$field])) {
            return $value[$field];
        }

        return $default;
    }

    /**
     * Set an entity field value
     *
     * @param $field
     * @param null $value
     * @return mixed
     */
    public function set($field, $value = null)
    {

        $this->call($field, null, [$value], 'set', false);

        if ($this->get($field) === $value) {
            return $this;
        }

        $this->call('set', null, [$field, $value]);

        if ($this->get($field) === $value) {
            return $value;
        }

        $data = $this->call('data', null, [], 'get');

        if (!is_array($data)) {
            return $this;
        }

        $data[$field] = $value;

        $this->call('data', null, [$data], 'set');

        return $this;
    }
}