<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 01.03.2016
 * Time: 09:33
 *
 */

namespace Blast\Orm\Entity;


use Blast\Orm\Data\AccessorInterface;
use Blast\Orm\Data\AccessorTrait;
use Blast\Orm\Data\DataObject;
use Blast\Orm\Data\MutatorInterface;
use Blast\Orm\Data\MutatorTrait;
use Blast\Orm\Mapper;
use Blast\Orm\MapperAwareInterface;
use Blast\Orm\MapperInterface;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;

class GenericEntity extends DataObject implements AccessorInterface, FieldAwareInterface, IndexAwareInterface,
    MapperAwareInterface, MutatorInterface, PrimaryKeyAwareInterface, TableNameAwareInterface
{

    use AccessorTrait;
    use MutatorTrait;

    /**
     * @var Column[]
     */
    public static $fields = [];

    /**
     * @var Index[]
     */
    public static $indexes = [];

    /**
     * @var MapperInterface
     */
    public static $mapper = null;

    /**
     * @var string
     */
    public static $primaryKeyName = EntityAdapterInterface::DEFAULT_PRIMARY_KEY_NAME;

    /**
     * @var string
     */
    public static $tableName = null;

    public function __construct($tableName, array $options = [])
    {
        self::$tableName = $tableName;

    }

    /**
     * @return \Doctrine\DBAL\Schema\Column[]
     *
     *
     * Currently not supported
     * @codeCoverageIgnore
     */
    public function getFields()
    {
        return static::$fields;
    }

    /**
     * @return \Doctrine\DBAL\Schema\Index[]
     *
     * Currently not supported
     * @codeCoverageIgnore
     */
    public function getIndexes()
    {
        return static::$indexes;
    }

    /**
     * @return MapperInterface
     */
    public function getMapper()
    {
        if(static::$mapper === null){
            static::$mapper = new Mapper($this);
        }
        return static::$mapper;
    }

    /**
     * @return string
     */
    public function getPrimaryKeyName()
    {
        return static::$primaryKeyName;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return static::$tableName;
    }

}