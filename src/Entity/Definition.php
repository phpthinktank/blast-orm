<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 17.03.2016
 * Time: 08:17
 *
 */

namespace Blast\Orm\Entity;


use Blast\Orm\ConnectionManager;
use Blast\Orm\EventEmitterFactoryInterface;
use Blast\Orm\EventEmitterFactoryTrait;
use Blast\Orm\Mapper;
use Blast\Orm\MapperFactoryInterface;
use Blast\Orm\MapperFactoryTrait;
use Blast\Orm\MapperInterface;
use Blast\Orm\Relations\RelationInterface;
use League\Event\EmitterAwareInterface;
use League\Event\EmitterAwareTrait;

class Definition implements DefinitionInterface, EventEmitterFactoryInterface, MapperFactoryInterface
{

    use EmitterAwareTrait;
    use EventEmitterFactoryTrait;
    use MapperFactoryTrait;

    private $configuration = [
        'entity' => \ArrayObject::class,
        'entityCollection' => \SplStack::class,
        'events' => [],
        'fields' => [],
        'indexes' => [],
        'primaryKeyName' => ProviderInterface::DEFAULT_PRIMARY_KEY_NAME,
        'tableName' => '',
        'mapper' => Mapper::class,
        'relations' => [],
    ];

    /**
     * @return array
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Add additional configuration. Configuration will be merged into
     *
     * @param array $configuration
     * @return Definition
     */
    public function setConfiguration(array $configuration)
    {
        $this->mergeConfiguration($configuration);

        return $this;
    }

    /**
     * Get the entity object.
     *
     * @return \ArrayObject|object
     */
    public function getEntity()
    {
        if (!is_object($this->configuration['entity'])) {
            $entity = $this->configuration['entity'];
            $this->configuration['entity'] = new $entity;
        }
        return $this->configuration['entity'];
    }

    /**
     * Get the custom entity collection
     *
     * @return \SplStack|object
     */
    public function getEntityCollection()
    {
        if (!is_object($this->configuration['entityCollection'])) {
            $entity = $this->configuration['entityCollection'];
            $this->configuration['entityCollection'] = new $entity;
        }
        return $this->configuration['entityCollection'];
    }

    /**
     * Load event emitter. If entity has events and
     * no emitter exists, create a new emitter.
     *
     * @return \ArrayObject|object
     */
    public function getEmitter()
    {
        if (null === $this->emitter) {
            if (!empty($this->configuration['events'])) {
                $entity = $this->getEntity();
                if ($entity instanceof EmitterAwareInterface) {
                    $emitter = $this->createEventEmitter($this->configuration['events'], $entity->getEmitter());
                } else {
                    $emitter = $this->createEventEmitter($this->configuration['events']);
                }
            } else {
                $emitter = $this->createEventEmitter();
            }

            $this->setEmitter($emitter);
        }
        return $this->emitter;
    }

    /**
     * Get fields
     *
     * @return \Doctrine\DBAL\Schema\Column[]
     */
    public function getFields()
    {
        return $this->configuration['fields'];
    }

    /**
     * Get indexes
     *
     * @return \Doctrine\DBAL\Schema\Index[]
     */
    public function getIndexes()
    {
        return $this->configuration['indexes'];
    }

    /**
     * Get name of primary key
     *
     * @return string
     */
    public function getPrimaryKeyName()
    {
        return $this->configuration['primaryKeyName'];
    }

    /**
     * Get table name
     *
     * Add prefix if if $withPrefix is true and a prefix exists
     *
     * @param bool $withPrefix
     * @return string
     */
    public function getTableName($withPrefix = true)
    {
        $prefix = ConnectionManager::getInstance()->get()->getPrefix();
        return (true === $withPrefix && strlen($prefix) > 0 ? rtrim($prefix, '_') . '_' : '') . $this->configuration['tableName'];
    }

    /**
     * Get entity mapper
     *
     * @return MapperInterface
     */
    public function getMapper()
    {
        if (!($this->configuration['mapper'] instanceof MapperInterface)) {
            $this->configuration['mapper'] = $this->createMapper($this);
        }
        return $this->configuration['mapper'];
    }

    /**
     * Get an array of relations
     *
     * @return RelationInterface[]
     */
    public function getRelations()
    {
        return $this->configuration['relations'];
    }

    /**
     * Merge partial configuration into definition configuration. Normalize partial
     * configuration keys before add them to configuration. Add custom configuration
     * after adding known configuration.
     *
     * @param array $configuration
     *
     * @return $this
     */
    private function mergeConfiguration(array $configuration)
    {
        $originalConfiguration = $configuration;

        // normalize keys to lower case
        foreach ($configuration as $key => $value) {
            $configuration[strtolower($key)] = $value;
        }

        // add definition configuration by it's real key
        $configKeys = array_keys($this->configuration);
        foreach ($configKeys as $key) {
            $normalizedKey = strtolower($key);
            if (isset($configuration[$normalizedKey])) {
                $this->configuration[$key] = $configuration[$normalizedKey];
                unset($configuration[$normalizedKey]);
            }
        }

        // add custom configuration by it's original key
        foreach ($originalConfiguration as $key => $value) {
            $normalizedKey = strtolower($key);
            if (isset($configuration[$normalizedKey])) {
                $this->configuration[$key] = $originalConfiguration[$key];
            }
        }

        return $this;
    }
}
