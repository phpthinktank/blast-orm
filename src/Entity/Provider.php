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
use Blast\Orm\Mapper;
use Blast\Orm\MapperFactoryInterface;
use Blast\Orm\MapperFactoryTrait;
use Blast\Orm\MapperInterface;
use Blast\Orm\Relations\RelationInterface;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;

class Provider implements ProviderInterface
{

    use EntityAwareTrait;

    /**
     * Entity definition
     *
     * @var DefinitionInterface
     */
    private $definition;

    /**
     * Provider constructor.
     *
     * @param $tableName
     */
    public function __construct($tableName)
    {
        $transformer = new Transformer();
        $transformer->transform($tableName);

        $this->entity = $transformer->getEntity();
        $this->definition = $transformer->getDefinition();
    }

    /**
     * @return DefinitionInterface
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * @return \Doctrine\DBAL\Schema\Column[]
     */
    public function getFields()
    {
        return $this->getDefinition()->getFields();
    }

    /**
     * @return \Doctrine\DBAL\Schema\Index[]
     */
    public function getIndexes()
    {
        return $this->getDefinition()->getIndexes();
    }

    /**
     * @return MapperInterface|Mapper
     */
    public function getMapper()
    {
        return $this->getDefinition()->getMapper();
    }

    /**
     * @return \Blast\Orm\Relations\RelationInterface[]
     */
    public function getRelations()
    {
        return $this->getDefinition()->getRelations();
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->getDefinition()->getTableName();
    }

    /**
     * @return string
     */
    public function getPrimaryKeyName()
    {
        return $this->getDefinition()->getPrimaryKeyName();
    }

    /**
     * Convert data array to entity with data
     *
     * @param array $data
     * @param string $option
     * @return object
     */
    public function withData(array $data = [], $option = HydratorInterface::HYDRATE_AUTO)
    {
        return (new ArrayToObjectHydrator($this))->hydrate($data, $option);
    }

    /**
     * Convert object properties or object getter to data array
     *
     * @param array $additionalData
     * @return object
     */
    public function fetchData(array $additionalData = [])
    {
        return (new ObjectToArrayHydrator($this))->hydrate($additionalData);
    }

    /**
     * Check if entity is new or not
     *
     * @return bool
     */
    public function isNew()
    {
        $data = $this->fetchData();
        return isset($data[$this->getPrimaryKeyName()]) ? empty($data[$this->getPrimaryKeyName()]) : true;
    }
}
