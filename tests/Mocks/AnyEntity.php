<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 26.11.2015
 * Time: 17:04
 */

namespace Mocks;


use Blast\Orm\Entity\AbstractEntity;

class AnyEntity extends AbstractEntity
{
    protected $table = 'test';
    protected $primaryKeyField = 'id';
}