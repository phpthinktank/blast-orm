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


use Blast\Orm\CacheAwareTrait;
use Blast\Orm\Hydrator\EntityHydrator;
use Blast\Orm\Hydrator\HydratorInterface;
use Blast\Orm\Support;
use Doctrine\Common\Inflector\Inflector;

class Provider implements ProviderInterface
{

    use CacheAwareTrait;
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
        $transformer = $transformer = $this->transform($tableName);

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
    public function hydrate(array $data = [], $option = HydratorInterface::HYDRATE_AUTO)
    {
        return (new EntityHydrator($this))->hydrate($data, $option);
    }

    /**
     * Convert object properties or object getter to data array
     *
     * @param array $additionalData
     * @return array|\ArrayObject
     */
    public function extract(array $additionalData = [])
    {
        return array_replace_recursive($additionalData, (new EntityHydrator($this))->extract());
    }

    /**
     * Check if entity is new or not
     *
     * @return bool
     */
    public function isNew()
    {
        $data = $this->extract();

        return isset($data[$this->getDefinition()->getPrimaryKeyName()]) ? empty($data[$this->getDefinition()->getPrimaryKeyName()]) : true;
    }

    /**
     * Todo rewrite this horrobile piece of code...
     * @param $tableName
     * @return bool|string
     */
    private function determineCacheId($tableName)
    {
        /** @var string|bool $compTableName */
        if ($tableName instanceof DefinitionInterface) {
            return $tableName->getTableName();
        }
        if (null === $tableName) {
            return false;
        }
        if (is_string($tableName)) {
            return
                (class_exists($tableName) && false === Support::isPHPInternalClass($tableName))
                ? Inflector::pluralize(Inflector::tableize(Support::getCachedReflectionClass($tableName, $this->getReflectionCache())->getShortName()))
                : $tableName;
        }
        if (is_array($tableName)) {
            return false;
        }
        if(Support::isPHPInternalClass($tableName)){
            return false;
        }
        if ($tableName instanceof EntityAwareInterface) {
            $compTableName = Inflector::pluralize(Inflector::tableize(Support::getCachedReflectionClass(Support::getEntityName($tableName->getEntity()), $this->getReflectionCache())->getShortName()));
            if (is_string($compTableName)) {
                return $compTableName;
            }
        }
        if (is_object($tableName)) {
            $compTableName = Inflector::pluralize(Inflector::tableize(Support::getCachedReflectionClass(Support::getEntityName($tableName), $this->getReflectionCache())->getShortName()));

            if (is_string($compTableName)) {
                return $compTableName;
            }
        }

        return false;

    }

    /**
     * @param $tableName
     * @return Transformer
     */
    private function transform($tableName)
    {
        $transformer = new Transformer();
        $transformer->transform($tableName);
        return $transformer;
    }
}
