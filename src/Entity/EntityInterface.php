<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 25.11.2015
 * Time: 17:40
 */

namespace Blast\Orm\Entity;


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

}