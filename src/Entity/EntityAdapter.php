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
use Blast\Orm\Mapper;
use Blast\Orm\MapperInterface;
use Blast\Orm\Query;
use Blast\Orm\Relations\RelationInterface;
use Doctrine\DBAL\Driver\Statement;
use League\Event\EmitterAwareTrait;

class EntityAdapter extends DataAdapter implements EntityAdapterInterface
{

    use EmitterAwareTrait;

    /**
     * @var Query
     */
    private $query;

    private $mapper;

    /**
     * EntityAdapter constructor.
     * @param array|\stdClass|\ArrayObject|object|string $object
     */
    public function __construct($object = null)
    {
        $object = EntityAdapterCollectionFacade::createObject($object);
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
     * Get table name from entity. If no table name is declared determine from class name and convert camelcase to
     * underscore
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->access('tableName',
            ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $this->getReflection()->getShortName())), '_'),
            null, \ReflectionMethod::IS_STATIC);
    }

    /**
     * Get entity primary key name
     *
     * @return string
     */
    public function getPrimaryKeyName()
    {
        return $this->access('primaryKeyName', static::DEFAULT_PRIMARY_KEY_NAME, null, \ReflectionMethod::IS_STATIC);
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
        return $this->access('fields', [], null, \ReflectionMethod::IS_STATIC);
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
        return $this->access('index', [], null, \ReflectionMethod::IS_STATIC);
    }

    /**
     * Return an array of relations
     * @return RelationInterface[]
     */
    public function getRelations()
    {
        $reflection = $this->getReflection();
        $default = [];
        if (!$reflection->hasMethod('relations')) {
            return $default;
        }

        $method = $reflection->getMethod('relations');

        if (!$method->isStatic()) {
            return $default;
        }

        $relations = $method->invokeArgs($this->getObject(), [$this->getObject()]);

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
        $entity = NULL;

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
        return $this->mutate('query', $query, \ReflectionMethod::IS_STATIC);
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
        return $this->access('query', $this->query, \ReflectionMethod::IS_STATIC);
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
        return $this->access('mapper', $this->mapper, \ReflectionMethod::IS_STATIC);
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
}