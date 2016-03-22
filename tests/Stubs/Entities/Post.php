<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 26.11.2015
 * Time: 17:04
 */

namespace Blast\Tests\Orm\Stubs\Entities;

use Blast\Orm\Mapper;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Type;

/**
 * @codeCoverageIgnore
 */
class Post extends \ArrayObject
{

    /**
     * Get table for model
     *
     * @return string
     */
    public static function tablename()
    {
        return 'post';
    }

    public static function fields($entity, Mapper $mapper)
    {
        return [
            'date' => new Column('date', Type::getType(Type::DATETIME), ['default' => new \DateTime()])
        ];
    }
}
