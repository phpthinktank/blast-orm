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
use Blast\Orm\Data\DataHydratorInterface;
use Blast\Orm\Data\DataObject;
use Blast\Orm\Query;
use Doctrine\DBAL\Driver\Statement;
use League\Event\EmitterAwareTrait;

class EntityAdapter extends DataAdapter implements EntityAdapterInterface, DataHydratorInterface
{
    const DEFAULT_PRIMARY_KEY_NAME = 'id';

    /**
     *
     */
    const RESULT_COLLECTION = 'collection';
    /**
     *
     */
    const RESULT_ENTITY = 'entity';

    /**
     *
     */
    const RESULT_RAW = 'raw';

    use EmitterAwareTrait;

    /**
     * @var Query
     */
    private $query;

    /**
     * EntityAdapter constructor.
     * @param array|\stdClass|\ArrayObject|object|string $object
     */
    public function __construct($object = null)
    {
        if(!is_object($object)){
            $object = new Query\Result();
        }
        $this->setObject($object);
    }

    public function getClassName()
    {
        return $this->getReflection()->getName();
    }

    public function getTableName()
    {
        return $this->access('tableName', $this->getReflection()->getShortName(), \ReflectionMethod::IS_STATIC);
    }

    public function getPrimaryKeyName()
    {
        return $this->access('primaryKeyName', static::DEFAULT_PRIMARY_KEY_NAME, \ReflectionMethod::IS_STATIC);
    }

    public function getFields()
    {
        return $this->access('fields', [], \ReflectionMethod::IS_STATIC);
    }

    public function getIndexes()
    {
        return $this->access('index', [], \ReflectionMethod::IS_STATIC);
    }

    public function getRelations()
    {
        return $this->access('relations', [], \ReflectionMethod::IS_STATIC);
    }

    public function hydrate($data = [], $option = self::AUTO)
    {
        if ($this->isRaw($option)) {
            return $data;
        }

        $count = count($data);
        $entity = NULL;

        if ($option === self::AUTO) {
            $option = $count > 1 || $count === 0 ? self::RESULT_COLLECTION : self::RESULT_ENTITY;
        }

        if ($option === self::RESULT_COLLECTION) { //if entity set has many items, return a collection of entities
            foreach ($data as $key => $value) {
                $data[$key] = $this->map($value);
            }
            $entity = new DataObject();
            $entity->setData($data);
        } elseif ($option === self::RESULT_ENTITY) { //if entity has one item, return the entity
            $entity = $this->map(array_shift($data));
        }

        return $entity;
    }

    public function setQuery(Query $query)
    {
        $this->query = $query;
        return $this->mutate('query', $query, \ReflectionMethod::IS_STATIC);
    }

    public function getQuery()
    {
        return $this->access('query', $this->query, \ReflectionMethod::IS_STATIC);
    }

    /**
     * Pass data to result or model
     * @param array $data
     * @return array|\stdClass|\ArrayObject|object
     */
    protected function map($data = [])
    {
        $this->setData($data);
        $object = $this->getObject();
        $this->reset();
        return $object;
    }

    /**
     * @param $option
     * @return bool
     */
    protected function isRaw($option)
    {
        $data = $this->getData();
        return $option === self::RESULT_RAW ||
        $data instanceof Statement ||
        is_numeric($data) ||
        is_bool($data);
    }
}