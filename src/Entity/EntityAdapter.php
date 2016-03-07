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
use Blast\Orm\LocatorFacade;
use Blast\Orm\Mapper;
use Blast\Orm\MapperInterface;
use Blast\Orm\Query;
use Blast\Orm\Relations\RelationInterface;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;
use League\Event\EmitterAwareTrait;

class EntityAdapter extends DataAdapter implements EntityAdapterInterface
{

    use EmitterAwareTrait;

    /**
     * @var MapperInterface
     */
    private $mapper;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var $primaryKeyName
     */
    private $primaryKeyName;

    /**
     * @var Column[]
     */
    private $fields;

    /**
     * @var Index[]
     */
    private $indexes;

    /**
     * @var RelationInterface[]
     */
    private $relations;

    /**
     * EntityAdapter constructor.
     * @param array|\stdClass|\ArrayObject|object|string $object
     */
    public function __construct($object = null)
    {
        if($object instanceof Definition){
            $this->fields = $object->getFields();
            $this->indexes = $object->getIndexes();
            $this->mapper = $object->getMapper();
            $this->primaryKeyName = $object->getPrimaryKeyName();
            $this->tableName = $object->getTableName();
            $this->relations = $object->getRelations();
            $object = $object->getEntity();
        }
        $object = LocatorFacade::getAdapterManager()->createObject($object);
        parent::__construct($object);
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
     * Get entity fields
     *
     * @return \Doctrine\DBAL\Schema\Column[]
     *
     * Currently not supported
     * @codeCoverageIgnore
     */
    public function getFields()
    {
        if(empty($this->fields)){
            $this->fields = $this->getDefinition('fields', []);
        }
        return $this->fields;
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
        if(empty($this->indexes)){
            $this->indexes = $this->getDefinition('index', []);
        }
        return $this->indexes;
    }

    /**
     * Get entity mapper
     *
     * @return MapperInterface
     */
    public function getMapper()
    {
        if (empty($this->mapper)) {
            $this->mapper = $this->getDefinition('mapper', new Mapper($this));
        }

        return $this->mapper;
    }

    /**
     * Get entity primary key name
     *
     * @return string
     */
    public function getPrimaryKeyName()
    {
        if(empty($this->primaryKeyName)){
            $this->primaryKeyName = $this->getDefinition('primaryKeyName', static::DEFAULT_PRIMARY_KEY_NAME);
        }
        return $this->primaryKeyName;
    }

    /**
     * Return an array of relations
     *
     * @return RelationInterface[]
     */
    public function getRelations()
    {
        if(empty($this->relations)){
            $relations = $this->callDefinition('relations', [], [$this->getObject()], 'get');

            $processedRelations = [];
            foreach ($relations as $name => $relation) {
                if (!($relation instanceof RelationInterface)) {
                    continue;
                }
                if (is_numeric($name)) {
                    $name = $relation->getName();
                }

                //relations must not overwrite data fields by name
                if (!isset($processedRelations[$name])) {
                    $processedRelations[$name] = $relation;
                }
            }

            $this->relations = is_array($processedRelations) ? $processedRelations : [];
        }

        return $this->relations;
    }

    /**
     * Get table name from entity. If no table name is declared determine from class name and convert camelcase to
     * underscore
     *
     * @return string
     */
    public function getTableName()
    {
        if(empty($this->tableName)){
            $this->tableName = $this->getDefinition('tableName', $this->camelCaseToUnderScore($this->getReflection()->getShortName()));
        }
        return $this->tableName;
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
            $this->getObject() instanceof Definition ?
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

    /**
     * Get definition from entity
     * @param $methodOrProperty
     * @param null $default
     * @return mixed
     */
    public function getDefinition($methodOrProperty, $default = null)
    {
        $definition = $this->callDefinition($methodOrProperty, $default, [], 'get');
        return null === $definition ? $default : $definition;
    }

    /**
     * Set definition
     *
     * @param $methodOrProperty
     * @param null $value
     * @return $this
     */
    public function setDefinition($methodOrProperty, $value = null)
    {
        $this->callDefinition($methodOrProperty, null, [$value], 'get');

        return $this;
    }
}