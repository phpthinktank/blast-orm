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
     * Convert data array to entity with data
     *
     * @param array $data
     * @param string $option
     * @return object|\ArrayObject
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
        return isset($data[$this->getDefinition()->getPrimaryKeyName()]) ? empty($data[$this->getDefinition()->getPrimaryKeyName()]) : true;
    }
}
