<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 17.03.2016
 * Time: 08:05
 *
 */

namespace Blast\Orm\Entity;

use Doctrine\Common\Inflector\Inflector;

class Transformer implements TransformerInterface, EntityAwareInterface
{

    use EntityAwareTrait;

    /**
     * @var \Blast\Orm\Entity\DefinitionInterface
     */
    private $definition = null;

    /**
     * Transform configuration into entity and entity definition. Configuration could be a
     * string (class name or table name), array (convert to a definition),
     * a Definition instance or an Entity instance.
     *
     * @param $configuration
     * @return mixed
     */
    public function transform($configuration)
    {
        if($configuration instanceof DefinitionInterface){
            $this->definition = $configuration;
            $this->setEntity($this->getDefinition()->getEntity());
            return $this;
        }
        if(null === $configuration){
            $configuration = \ArrayObject::class;
        }
        if (is_string($configuration)) {
            $configuration = $this->transformStringToDefinitionArray($configuration);
        }
        if (is_array($configuration)) {
            $this->definition = $this->transformArrayToDefinition($configuration);
            $this->setEntity($this->getDefinition()->getEntity());

            return $this;
        }
        if ($configuration instanceof EntityAwareInterface) {
            $this->setEntity($configuration->getEntity());
            $this->definition = $this->transformEntityToDefinition($this->getEntity());
        }
        if (is_object($configuration)) {
            $this->setEntity($configuration);
            $this->definition = $this->transformEntityToDefinition($this->getEntity());
        }

        return $this;

    }

    /**
     * @return DefinitionInterface
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    private function transformStringToDefinitionArray($configuration)
    {
        $tableOrClass = $configuration;
        $configuration = [];

        if (class_exists($tableOrClass)) {
            $configuration['entity'] = new $tableOrClass;
            $configuration['tableName'] = $tableOrClass;
        }else{
            $configuration['tableName'] = $tableOrClass;
        }

        return $configuration;
    }

    /**
     * @param $configuration
     * @return Definition
     */
    private function transformArrayToDefinition($configuration)
    {
        $definition = (new Definition())->setConfiguration($configuration);
        // transform entity to definition and overwrite configuration
        return $this->transformEntityToDefinition($definition->getEntity(), $definition);
    }

    /**
     * @param $entity
     * @param \Blast\Orm\Entity\DefinitionInterface|null $definition
     * @return Definition
     */
    private function transformEntityToDefinition($entity, $definition = null)
    {
        // find definition class in entity by property or method
        $definition = $this->loadDefinitionFromEntity($entity, $definition);
        $reflection = new \ReflectionObject($entity);
        $configuration = $definition->getConfiguration();

        //mapper is needed to for events, therefore we need to fetch mapper first
        if ($reflection->hasMethod('mapper')) {
            $mapperMethod = $reflection->getMethod('mapper');
            if($mapperMethod->isStatic() && $mapperMethod->isPublic()){
                $configuration['mapper'] = $mapperMethod->invokeArgs($entity, [$entity]);
            }
        }

        //update configuration with mapper
        $configuration = $definition->setConfiguration($configuration)->getConfiguration();

        foreach ($configuration as $key => $value) {
            //ignore entity or mapper
            if (in_array($key, ['entity', 'mapper'])) {
                continue;
            }
            if ($reflection->hasMethod($key)) {
                $method = $reflection->getMethod($key);
                if ($method->isPublic() && $method->isStatic()) {
                    $value = $method->invokeArgs($entity, [$entity, $definition->getMapper()]);
                }
            } else {
                $value = is_callable($value) ?
                    call_user_func_array($value, [$entity, $definition->getMapper()]) :
                    $value;
            }
            $configuration[$key] = $value;
        }

        $configuration['entityClassName'] = $reflection->getName();
        $configuration['entityShortName'] = $reflection->getShortName();

        $definition->setConfiguration($configuration);

        // set table name from reflection short name
        // if table name is unknown or FQCN and if
        // entity is no ArrayObject
        if ((empty($definition->getTableName()) || class_exists($definition->getTableName()) ) && !($definition->getEntity() instanceof \ArrayObject)) {
            $configuration['tableName'] = Inflector::pluralize(Inflector::tableize($configuration['entityShortName']));
        }

        return $definition->setConfiguration($configuration);
    }

    /**
     * @param $entity
     * @param Definition|null $definition
     * @return Definition|null
     */
    private function loadDefinitionFromEntity($entity, $definition = null)
    {
        $definitionClass = null;
        if (property_exists($entity, 'definition')) {
            $definitionClass = $entity::definition;
        }
        if (method_exists($entity, 'definition')) {
            $definitionClass = $entity::definition();
        }
        if (null !== $definitionClass) {
            $definition = is_object($definitionClass) ? $definitionClass : new $definitionClass;
        }
        if (null === $definition) {
            $definition = new Definition();
        }

        $configuration = $definition->getConfiguration();
        $configuration['entity'] = $entity;
        $definition->setConfiguration($configuration);

        return $definition;
    }
}
