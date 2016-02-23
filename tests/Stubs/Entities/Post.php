<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 26.11.2015
 * Time: 17:04
 */

namespace Blast\Tests\Orm\Stubs\Entities;

use Blast\Orm\Data\DataObjectInterface;
use Blast\Orm\Data\ImmutableDataObjectTrait;
use Blast\Orm\Data\MutableDataObjectTrait;

class Post implements DataObjectInterface
{
    use MutableDataObjectTrait;
    use ImmutableDataObjectTrait;

    /**
     * Get table for model
     *
     * @return string
     */
    public static function getTable()
    {
        return 'post';
    }
}