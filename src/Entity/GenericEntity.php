<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 25.11.2015
 * Time: 17:48
 */

namespace Blast\Orm\Entity;


class GenericEntity extends AbstractEntity implements GenericEntityInterface
{

    /**
     * GenericEntity constructor.
     * @param string $table Name of given Table
     */
    public function __construct($table)
    {
        $this->setTable($table);
    }
}