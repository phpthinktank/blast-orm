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


use Blast\Orm\Relations\RelationInterface;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;

class GenericEntity implements TableNameAwareInterface, PrimaryKeyAwareInterface, FieldAwareInterface, IndexAwareInterface
{

    /**
     * @var Column[]
     */
    private $fields = [];

    /**
     * @var Index[]
     */
    private $indexes = [];

    /**
     * @var string
     */
    private $primaryKeyName = null;

    /**
     * @var RelationInterface[]
     */
    private $relations = [];

    /**
     * @var string
     */
    private $tableName = null;

    public function __construct($tableName, $options = null)
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
            }else{
                return false;
            }
        };

        foreach ($options as $key => $value) {
            $this->set($key, $value, $onBefore, $onLoop);
        }
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
        if (call_user_func_array($onBefore, [$propertyName, &$data])) {
            return;
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (call_user_func_array($onLoop, [$propertyName, &$value, &$key])) {
                    continue;
                }

                $this->$propertyName[$key] = $value;
            }
        }else{
            $this->$propertyName = $data;
        }
    }

    /**
     * @return \Doctrine\DBAL\Schema\Column[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return \Doctrine\DBAL\Schema\Index[]
     */
    public function getIndexes()
    {
        return $this->indexes;
    }

    /**
     * @return string
     */
    public function getPrimaryKeyName()
    {
        return $this->primaryKeyName;
    }

    /**
     * @return \Blast\Orm\Relations\RelationInterface[]
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

}