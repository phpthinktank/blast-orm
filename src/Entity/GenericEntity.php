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
    private static $primaryKeyName = EntityAdapterInterface::DEFAULT_PRIMARY_KEY_NAME;

    /**
     * @var string
     */
    private static $tableName = null;

    public function __construct($tableName, array $options = [])
    {
        // @codeCoverageIgnoreStart
        /**
         * @param $propertyName
         * @param $data
         * @return bool
         */
        $onBefore = function($propertyName, $data){
            return $propertyName === 'primaryKeyName' ? is_string($data) : is_array($data);
        };

        $onLoop = function($propertyName, $value){
            if($propertyName === 'fields'){
                return $value instanceof Column;
            }elseif($propertyName === 'indexes'){
                return $value instanceof Index;
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
            $this->setOption($key, $value, $onBefore, $onLoop);
        }
        // @codeCoverageIgnoreEnd

        $this->setOption('tableName', $tableName);
    }

    /**
     * Set data to property
     * @param $propertyName
     * @param $data
     * @param callable|null $onBefore
     * @param callable|null $onLoop
     */
    private function setOption($propertyName, $data, callable $onBefore = null, callable $onLoop = null)
    {
        $before = true;
        if(is_callable($onBefore)){
            $before = call_user_func_array($onBefore, [$propertyName, &$data]);
        }
        if (!$before) {
            return;
        }

        $property = new \ReflectionProperty($this, $propertyName);
        if(!$property->isPublic()){
            $property->setAccessible(true);
        }
        $propertyValue = $property->getValue($this);

        // @codeCoverageIgnoreStart
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $loop = true;
                if(is_callable($onLoop)){
                    $loop = call_user_func_array($onLoop, [$propertyName, &$value, &$key]);
                }
                if (!$loop) {
                    continue;
                }



                $propertyValue[$key] = $value;
            }
        }else{
            $propertyValue = $data;
        }
        // @codeCoverageIgnoreEnd

        $property->setValue($this, $propertyValue);
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