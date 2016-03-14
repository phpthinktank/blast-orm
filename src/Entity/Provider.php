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


use Blast\Orm\Facades\FacadeFactory;
use Blast\Orm\Hydrator\ArrayToObjectHydrator;
use Blast\Orm\Hydrator\HydratorInterface;
use Blast\Orm\Hydrator\ObjectToArrayHydrator;
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

    public function __construct($tableName)
    {
        $this->init($tableName);
        $this->define(is_array($tableName) ? $tableName : []);
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
     * @return MapperInterface
     */
    public function getMapper()
    {
        if ($this->mapper instanceof MapperInterface) {
            return $this->mapper;
        }
        $container = FacadeFactory::getContainer();
        if (!$container->has($this->mapper)) {
            $container->add($this->mapper, new Mapper($this->getEntity()));
        }
        return $container->get($this->mapper);
    }

    /**
     * @return string
     */
    public function getPrimaryKeyName()
    {
        return $this->primaryKeyName;
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
            $container = FacadeFactory::getContainer();
            if ($container->has($tableName)) {
                $this->entity = $container->get($tableName);
            } elseif (class_exists($tableName)) {
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

    protected function define(array $definition = [])
    {
        $defaultDefinition = get_object_vars($this);


        $entity = $this->getEntity();
        $reflection = new \ReflectionObject($entity);

        $methods = $reflection->getMethods(\ReflectionMethod::IS_STATIC | \ReflectionMethod::IS_PUBLIC);

        //mapper is needed to for events, therefore we need to fetch mapper first
        $mapper = null;
        if (isset($definition['mapper'])) {
            $mapper = $definition['mapper'];
            unset($definition['mapper']);
        } elseif (isset($methods['mapper'])) {
            $mapper = $methods['mapper']->invokeArgs($entity, [$entity]);
            unset($methods['mapper']);
        } else {
            if (FacadeFactory::getContainer()->has(MapperInterface::class)) {
                $mapper = FacadeFactory::getContainer()->get(MapperInterface::class);
            } else {
                $mapper = new Mapper($this);
            }
        }

        $this->mapper = $mapper;

        foreach ($defaultDefinition as $key => $value) {
            if (isset($definition[$key])) {
                $value = is_callable($definition[$key]) ?
                    call_user_func_array($definition[$key], [$entity, $mapper]) :
                    $definition[$key];
            } elseif (isset($methods[$key])) {
                $value = $methods[$key]->invokeArgs($entity, [$entity, $mapper]);
            }
            $this->{$key} = $value;
        }

        if (null === $this->tableName && $reflection->getName() !== \ArrayObject::class) {
            $this->tableName = ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $reflection->getShortName())), '_');
        }

        return $this;
    }

    public function getData(array $additionalData = [])
    {
        return (new ObjectToArrayHydrator($this->entity))->hydrate($additionalData);
    }

    public function setData(array $data = [], $option = HydratorInterface::HYDRATE_AUTO)
    {
        return (new ArrayToObjectHydrator($this->entity))->hydrate($data, $option);
    }

    public function isNew()
    {
        $data = $this->getData();
        return isset($data[$this->getPrimaryKeyName()]) ? empty($data[$this->getPrimaryKeyName()]) : true;
    }
}