<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 25.11.2015
 * Time: 17:40
 */

namespace Blast\Orm\Entity;


use Blast\Orm\MapperInterface;
use League\Event\EmitterInterface;

interface EntityInterface
{

    /**
     * @return null|string
     */
    public function getTable();

    /**
     * @return boolean
     */
    public function isNew();

    /**
     * @return string|int
     */
    public function primaryKey();

    /**
     * @return string
     */
    public function primaryKeyField();

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data);

    /**
     * @return array
     */
    public function getData();

    /**
     * @return array
     */
    public function fields();

    /**
     * @param EmitterInterface $emitter
     * @return
     */
    public function events(EmitterInterface $emitter);

    /**
     * @return MapperInterface
     */
    public function getMapper();

    /**
     * @param MapperInterface $mapper
     * @return $this
     */
    public function setMapper(MapperInterface $mapper);

}