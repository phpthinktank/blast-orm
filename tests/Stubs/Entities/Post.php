<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 26.11.2015
 * Time: 17:04
 */

namespace Blast\Tests\Orm\Stubs\Entities;

use Blast\Orm\Data\AccessorInterface;
use Blast\Orm\Data\AccessorTrait;
use Blast\Orm\Data\DataObjectInterface;
use Blast\Orm\Data\ImmutableDataObjectTrait;
use Blast\Orm\Data\MutableDataObjectTrait;
use Blast\Orm\Data\MutatorInterface;
use Blast\Orm\Data\MutatorTrait;

/**
 * @codeCoverageIgnore
 */
class Post implements DataObjectInterface, AccessorInterface, MutatorInterface
{
    use MutableDataObjectTrait;
    use AccessorTrait;
    use MutatorTrait;
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