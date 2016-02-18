<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 16.02.2016
 * Time: 14:11
 *
 */

namespace Blast\Db\Orm;


use Blast\Db\Manager;
use Blast\Db\Orm\Model\GenericModel;
use Blast\Db\Orm\Model\ModelAwareInterface;
use Blast\Db\Orm\Model\ModelEventsAwareInterface;
use Blast\Db\Orm\Model\ModelFieldsAwareInterface;
use Blast\Db\Orm\Model\ModelRelationAwareInterface;

class Factory
{
    private static $mappers = [];

    private static $models = [];

    /**
     * Create mapper or get an existing
     * @param $name
     * @return MapperInterface|mixed
     */
    public static function createMapper($name)
    {
        $container = Manager::getInstance()->getContainer();
        $model = static::createModel($name);

        if ($model instanceof MapperAwareInterface) {
            //get mapper from model
            $mapper = $model->getMapper();
        } else {
            //get default mapper by contract
            $mapper = $container->get(MapperInterface::class);

            if ($mapper instanceof MapperInterface) {
                throw new \RuntimeException('No mapper configured!');
            }
        }

        //add model to mapper
        if ($mapper instanceof ModelAwareInterface) {
            $mapper->setModel($model);
        }

        //attach fields to model
        if ($model instanceof ModelFieldsAwareInterface) {
            $model::attachFields($mapper, $model);
        }

        //attach relations to mapper
        if ($model instanceof ModelRelationAwareInterface) {
            $model::attachRelations($mapper, $model);
        }

        //attach events to mapper
        if ($model instanceof ModelEventsAwareInterface) {
            $model::attachEvents($mapper, $model);
        }

        static::$mappers[$name] = $mapper;

        return $mapper;

    }

    /**
     * Create a new model or get an existing
     *
     * @param $tableOrClass
     * @param array $fields
     * @return GenericModel|mixed|object
     */
    public static function createModel($tableOrClass, $fields = []){

        $name = is_object($tableOrClass) ? get_class($tableOrClass) : $tableOrClass;

        if(isset(static::$models[$name])){
            return static::$models[$name];
        }

        $container = Manager::getInstance()->getContainer();

        if ($container->has($tableOrClass)) {
            $model = $container->get($tableOrClass);
        } elseif (class_exists($tableOrClass)) {
            $model = (new \ReflectionClass($tableOrClass))->newInstance();
        } else {
            $model = new GenericModel($tableOrClass, $fields);
        }

        return $model;
        
    }

}