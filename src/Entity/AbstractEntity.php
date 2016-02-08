<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 25.11.2015
 * Time: 17:48
 */

namespace Blast\Db\Entity;

use Blast\Db\Entity\Traits\DataConverterTrait;
use Blast\Db\Entity\Traits\EntityTrait;

abstract class AbstractEntity implements EntityInterface
{

    use EntityTrait;
    use DataConverterTrait;

    public function __construct()
    {
        $this->configure();
        $this->attachDefaultValues();
    }

    /**
     * Configure entity
     */
    abstract public function configure();

}