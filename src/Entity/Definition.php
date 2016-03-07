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



use Blast\Orm\MapperInterface;
use Blast\Orm\Relations\RelationInterface;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;

class Definition implements DefinitionInterface
{
    use EntityAwareTrait;

    /**
     * @var Column[]
     */
    private $fields = [];

    /**
     * @var Index[]
     */
    private $indexes = [];

    /**
     * @var MapperInterface
     */
    private $mapper = null;

    /**
     * @var string
     */
    private $primaryKeyName = EntityAdapterInterface::DEFAULT_PRIMARY_KEY_NAME;

    /**
     * @var string
     */
    private $tableName = null;

    /**
     * @var RelationInterface[]
     */
    private $relations = [];

    public function __construct($tableName)
    {
        $entity = null;
        $this->tableName = $tableName;

        //determine table is an entity
        if(is_object($tableName)){
            $entity = $tableName;
            $this->tableName = null;
        }elseif(is_string($tableName)){
            if(class_exists($tableName)){
                $entity = $tableName;
                $this->tableName = null;
            }
        }

        if(null === $entity){
            $entity = new Entity();
        }
        $this->setEntity($entity);
    }

    /**
     * @return \Doctrine\DBAL\Schema\Column[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param \Doctrine\DBAL\Schema\Column[] $fields
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return \Doctrine\DBAL\Schema\Index[]
     */
    public function getIndexes()
    {
        return $this->indexes;
    }

    /**
     * @param \Doctrine\DBAL\Schema\Index[] $indexes
     */
    public function setIndexes($indexes)
    {
        $this->indexes = $indexes;
    }

    /**
     * @return MapperInterface
     */
    public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * @param MapperInterface $mapper
     */
    public function setMapper($mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * @return string
     */
    public function getPrimaryKeyName()
    {
        return $this->primaryKeyName;
    }

    /**
     * @param string $primaryKeyName
     */
    public function setPrimaryKeyName($primaryKeyName)
    {
        $this->primaryKeyName = $primaryKeyName;
    }

    /**
     * @return \Blast\Orm\Relations\RelationInterface[]
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * @param \Blast\Orm\Relations\RelationInterface[] $relations
     */
    public function setRelations($relations)
    {
        $this->relations = $relations;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @param string $tableName
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }
}