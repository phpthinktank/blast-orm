<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 01.03.2016
 * Time: 09:33
 *
 */

namespace Blast\Orm\Entity;


use Blast\Orm\Hydrator\ArrayToObjectHydrator;
use Blast\Orm\Hydrator\HydratorInterface;
use Blast\Orm\Hydrator\ObjectToArrayHydrator;
use Blast\Orm\LocatorAwareTrait;
use Blast\Orm\LocatorInterface;
use Blast\Orm\Mapper;
use Blast\Orm\MapperInterface;
use Blast\Orm\Relations\RelationInterface;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;

class Provider implements ProviderInterface
{

    use EntityAwareTrait;

    /**
     * @var Column[]
     */
    private $fields = [];

    /**
     * @var Index[]
     */
    private $indexes = [];

    /**
     * @var string|MapperInterface
     */
    private $mapper = MapperInterface::class;

    /**
     * @var string
     */
    private $primaryKeyName = self::DEFAULT_PRIMARY_KEY_NAME;

    /**
     * @var string
     */
    private $tableName = null;

    /**
     * @var RelationInterface[]
     */
    private $relations = [];

    /**
     * Provider constructor.
     *
     * @param $tableName
     */
    public function __construct($tableName)
    {
        $this->init($tableName);
        $this->define(is_array($tableName) ? $tableName : []);
    }

    /**
     * Initialize provider
     *
     * @param $tableName
     *
     * @return $this
     */
    protected function init($tableName)
    {

        if (is_object($tableName)) {
            $this->entity = $tableName;
            return $this;
        }

        if (is_string($tableName)) {
            if (class_exists($tableName)) {
                $this->entity = new $tableName;
            } else {
                $this->entity = new \ArrayObject();
                $this->tableName = $tableName;
            }

            return $this;
        }

        $this->entity = new \ArrayObject();
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * @param array $definition
     * @return $this
     */
    protected function define(array $definition = [])
    {
        $entity = $this->getEntity();
        $reflection = new \ReflectionObject($entity);

        //mapper is needed to for events, therefore we need to fetch mapper first
        $this->findMapper($definition, $reflection, $entity);

        $defaultDefinition = get_object_vars($this);
        $definition = array_merge($defaultDefinition, $definition);


        foreach ($definition as $key => $value) {
            if ('mapper' === $key) {
                continue;
            }
            if ($reflection->hasMethod($key)) {
                $method = $reflection->getMethod($key);
                if ($method->isPublic() && $method->isStatic()) {
                    $value = $method->invokeArgs($entity, [$entity, $this->getMapper()]);
                }
            } else {
                $value = is_callable($value) ?
                    call_user_func_array($value, [$entity, $this->getMapper()]) :
                    $value;
            }
            $this->{$key} = $value;
        }

        if (null === $this->tableName && $reflection->getName() !== \ArrayObject::class) {
            $this->tableName = ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $reflection->getShortName())), '_');
        }

        return $this;
    }

    /**
     * @param array $definition
     * @param $reflection
     * @param $entity
     * @return array
     */
    protected function findMapper(array $definition, \ReflectionClass $reflection, $entity)
    {

        if (isset($definition['mapper'])) {
            $this->mapper = $definition['mapper'];
        }elseif ($reflection->hasMethod('mapper')) {
            $this->mapper = $reflection->getMethod('mapper')->invokeArgs($entity, [$entity]);
        }

        return $this;

    }

    /**
     * @return \Doctrine\DBAL\Schema\Column[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return \Doctrine\DBAL\Schema\Index[]
     */
    public function getIndexes()
    {
        return $this->indexes;
    }

    /**
     * @return MapperInterface|Mapper
     */
    public function getMapper()
    {
        if (!($this->mapper instanceof MapperInterface)){
            $this->mapper = new Mapper($this);
        }
        return $this->mapper;
    }

    /**
     * @return \Blast\Orm\Relations\RelationInterface[]
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        if (null === $this->tableName) {
            throw new \LogicException('Unable to get table name from entity');
        }
        return $this->tableName;
    }

    /**
     * Convert array to object properties or object setter
     *
     * @param array $data
     * @param string $option
     * @return mixed
     */
    public function fromArrayToObject(array $data = [], $option = HydratorInterface::HYDRATE_AUTO)
    {
        return (new ArrayToObjectHydrator($this))->hydrate($data, $option);
    }

    /**
     * Check if entity is new or not
     *
     * @return bool
     */
    public function isNew()
    {
        $data = $this->fromObjectToArray();
        return isset($data[$this->getPrimaryKeyName()]) ? empty($data[$this->getPrimaryKeyName()]) : true;
    }

    /**
     * Convert object properties or object getter to array
     *
     * @param array $additionalData
     * @return mixed
     */
    public function fromObjectToArray(array $additionalData = [])
    {
        return (new ObjectToArrayHydrator($this))->hydrate($additionalData);
    }

    /**
     * @return string
     */
    public function getPrimaryKeyName()
    {
        return $this->primaryKeyName;
    }
}
