<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 25.11.2015
 * Time: 17:51
 */

namespace Blast\Orm\Entity;


interface GenericEntityInterface
{

    /**
     * GenericEntity constructor.
     * @param string $table Name of given Table
     */
    public function __construct($table);

}