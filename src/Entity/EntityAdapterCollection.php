<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 03.03.2016
 * Time: 10:05
 *
 */

namespace Blast\Orm\Entity;


use Blast\Orm\Facades\FacadeFactory;
use Blast\Orm\Object\InvalidObjectFromStringException;
use Blast\Orm\Object\ObjectAdapterCollection;
use Blast\Orm\Query\Result;

class EntityAdapterCollection extends ObjectAdapterCollection
{
    /**
     * @param null $object
     * @param null $adapterClassName
     * @return EntityAdapter
     * @throws \Exception
     */
    public function get($object, $adapterClassName = null)
    {
        if(!is_subclass_of($adapterClassName, EntityAdapter::class)){
            $adapterClassName = EntityAdapter::class;
        }
        return parent::get($object, $adapterClassName);
    }


    /**
     * @param $object
     * @return Result|mixed|object
     */
    public function createObject($object)
    {
        // this is very specific to container
        // @coverageIgnoreStart
        if (is_string($object)) {
            $container = FacadeFactory::getContainer();
            if ($container->has($object)) {
                $object = $container->get($object);
            }
        }
        // @coverageIgnoreEnd

        try{
            $object = parent::createObject($object);
        }catch(InvalidObjectFromStringException $e){

        }

        $object = is_string($object) ? new GenericEntity($object) : $object;

        if(!is_object($object)){
            $object = new Result();
        }

        return $object;
    }
}