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


use Blast\Orm\Hydrator\Hydrator;
use Blast\Orm\Hydrator\HydratorInterface;

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
        return (new Hydrator($this))->hydrate($data, $option);
    }

    /**
     * Convert object properties or object getter to data array
     *
     * @param array $additionalData
     * @return object
     */
    public function fetchData(array $additionalData = [])
    {
        return (new Hydrator($this))->extract($additionalData);
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
