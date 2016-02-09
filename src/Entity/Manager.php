<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 25.01.2016
 * Time: 13:42
 *
 */

namespace Blast\Db\Entity;



use Blast\Db\Factory;
use Blast\Db\Orm\MapperInterface;

class Manager implements ManagerInterface
{

    /**
     * @var EntityInterface
     */
    private $entity;

    /**
     * @var Factory
     */
    private $factory;
    /**
     * @var MapperInterface
     */
    private $mapper;

    /**
     * Manager constructor.
     * @param string|EntityInterface $entity
     * @param MapperInterface $mapper
     * @param Factory $factory
     */
    public function __construct($entity)
    {
        $this->factory = Factory::getInstance();
        $this->entity = $this->composeEntityClass($entity);
    }

    /**
     * @return EntityInterface
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param EntityInterface $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return Factory
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * @param Factory $factory
     */
    public function setFactory($factory)
    {
        $this->factory = $factory;
    }

    /**
     *
     * @param EntityInterface|array $previous
     * @return EntityInterface
     */
    public function create($previous = null)
    {
        $entity = $this->composeEntityClass($this->getEntity());

        if(is_array($previous)){
            $previous = $this->composeEntityClass($this->getEntity())->setData($previous);
        }

        //overwrite data from previous entity, if previous is similar entity
        if($previous == $entity){
            $entity->setData($previous instanceof EntityInterface ? $previous->getData() : $previous);
        }

        return $entity;
    }

    /**
     * @param string|EntityInterface $entity
     * @return EntityInterface
     */
    protected function composeEntityClass($entity)
    {
        $entity = $this->factory->getContainer()->get($entity);

        if (!($entity instanceof EntityInterface)) {
            throw new \RuntimeException(sprintf('Connection needs to be an instance of %s', EntityInterface::class));
        }

        //we need to create a new object from given class
        //entities should not be static
        return (new \ReflectionObject($entity))->newInstance();
    }

}