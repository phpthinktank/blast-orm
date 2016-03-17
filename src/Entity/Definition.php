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
        'relations' => []
    ];

    /**
     * @return array
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param array $configuration
     * @return Definition
     */
    public function setConfiguration(array $configuration)
    {
        $this->configuration = array_merge($this->configuration, $configuration);
        return $this;
    }

    /**
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
     * Loads event emitter. If entity has events and no emitter exists, a new emitter
     *
     * @return \ArrayObject|object
     */
    public function getEmitter()
    {
        if(null === $this->emitter){
            if (!empty($this->configuration['events'])) {
                $entity = $this->getEntity();
                if($entity instanceof EmitterAwareInterface){
                    $emitter = $this->createEventEmitter($this->configuration['events'], $entity->getEmitter());
                }else{
                    $emitter = $this->createEventEmitter($this->configuration['events']);
                }
            }else{
                $emitter = $this->createEventEmitter();
            }

            $this->setEmitter($emitter);
        }
        return $this->emitter;
    }

    /**
     * @return \Doctrine\DBAL\Schema\Column[]
     */
    public function getFields()
    {
        return $this->configuration['fields'];
    }

    /**
     * @return \Doctrine\DBAL\Schema\Index[]
     */
    public function getIndexes()
    {
        return $this->configuration['indexes'];
    }

    /**
     * Name of primary key
     *
     * @return string
     */
    public function getPrimaryKeyName()
    {
        return $this->configuration['primaryKeyName'];
    }

    /**
     * Table name
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->configuration['tableName'];
    }

    /**
     * Get entity mapper
     *
     * @return MapperInterface
     */
    public function getMapper()
    {
        if(!($this->configuration['mapper'] instanceof MapperInterface)){
            $this->configuration['mapper'] = $this->createMapper($this->getEntity());
        }
        return $this->configuration['mapper'];
    }

    /**
     * @return RelationInterface[]
     */
    public function getRelations()
    {
        return $this->configuration['relations'];
    }
}
