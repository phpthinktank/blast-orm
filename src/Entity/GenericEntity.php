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


use Blast\Orm\Mapper;
use Blast\Orm\MapperAwareInterface;
use Blast\Orm\MapperInterface;
use Blast\Orm\Relations\RelationInterface;
use Blast\Orm\Relations\RelationsAwareInterface;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;

class GenericEntity implements FieldAwareInterface, IndexAwareInterface, MapperAwareInterface,
    PrimaryKeyAwareInterface, RelationsAwareInterface, TableNameAwareInterface
{

    /**
     * @var Column[]
     */
    private static $fields = [];

    /**
     * @var Index[]
     */
    private static $indexes = [];

    /**
     * @var MapperInterface
     */
    private static $mapper = null;

    /**
     * @var string
     */
    private static $primaryKeyName = null;

    /**
     * @var RelationInterface[]
     */
    private static $relations = [];

    /**
     * @var string
     */
    private static $tableName = null;

    public function __construct($tableName, array $options = [])
    {
        $onBefore = function($propertyName, $data){
            return $propertyName === 'primaryKeyName' ? is_string($data) : is_array($data);
        };

        $onLoop = function($propertyName, $value){
            if($propertyName === 'fields'){
                return $value instanceof Column;
            }elseif($propertyName === 'indexes'){
                return $value instanceof Index;
            }elseif($propertyName === 'relations'){
                return $value instanceof RelationInterface;
            }elseif($propertyName === 'mapper'){
                if(class_exists($value)){
                    if(!is_object($value)){
                        $value = new $value($this);
                    }
                }
                return $value instanceof MapperInterface;
            }else{
                return false;
            }
        };

        foreach ($options as $key => $value) {
            $this->set($key, $value, $onBefore, $onLoop);
        }

        $this->set('tableName', $tableName);
    }

    /**
     * Set data to property
     * @param $propertyName
     * @param $data
     * @param callable|null $onBefore
     * @param callable|null $onLoop
     */
    private function set($propertyName, $data, callable $onBefore = null, callable $onLoop = null)
    {
        $before = true;
        if(is_callable($onBefore)){
            $before = call_user_func_array($onBefore, [$propertyName, &$data]);
        }
        if (!$before) {
            return;
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $loop = true;
                if(is_callable($onLoop)){
                    $loop = call_user_func_array($onLoop, [$propertyName, &$value, &$key]);
                }
                if (!$loop) {
                    continue;
                }

                static::$$propertyName[$key] = $value;
            }
        }else{
            static::$$propertyName = $data;
        }
    }

    /**
     * @return \Doctrine\DBAL\Schema\Column[]
     */
    public function getFields()
    {
        return static::$fields;
    }

    /**
     * @return \Doctrine\DBAL\Schema\Index[]
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
     * @return \Blast\Orm\Relations\RelationInterface[]
     */
    public function getRelations()
    {
        return static::$relations;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return static::$tableName;
    }

}