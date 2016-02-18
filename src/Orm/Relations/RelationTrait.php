<?php
/*
*
* (c) Marco Bunge <marco_bunge@web.de>
*
* For the full copyright and license information, please view the LICENSE.txt
* file that was distributed with this source code.
*
* Date: 05.02.2016
* Time: 14:02
*/

namespace Blast\Db\Orm\Relations;

use Blast\Db\Orm\Factory;
use Blast\Db\Orm\MapperInterface;
use Blast\Db\Orm\Model\ModelInterface;
use Blast\Db\Orm\Model\PrimaryKeyAwareTrait;
use Blast\Db\Query\Result;
use Blast\Db\Query\ResultCollection;

trait RelationTrait
{
    /**
     * @var MapperInterface
     */
    protected $mapper;

    /**
     * @var object
     */
    protected $entity;

    /**
     * @var object
     */
    protected $foreignEntity;

    /**
     * @var integer|string
     */
    protected $foreignKey;

    /**
     * @var integer|string
     */
    protected $localKey;

    /**
     * @var ModelInterface|Result|ResultCollection
     */
    protected $results;

    /**
     * @var bool
     */
    protected $foreignEntityUpdate = false;

    /**
     * @return object
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param ModelInterface $model
     * @return RelationTrait
     */
    public function setEntity($model)
    {
        $this->entity = Factory::createModel($model);
        return $this;
    }

    /**
     * @return ModelInterface
     */
    public function getForeignEntity()
    {
        return $this->foreignEntity;
    }

    /**
     * @param ModelInterface $foreignEntity
     * @return $this
     */
    public function setForeignEntity($foreignEntity)
    {
        $this->foreignEntity = Factory::createModel($foreignEntity);
        return $this;
    }

    /**
     * @return int|string
     */
    public function getForeignKey()
    {
        if($this->foreignKey === null){
            $entity = $this->getForeignEntity();
            if($entity instanceof PrimaryKeyAwareTrait){
                $this->foreignKey = $entity->getPrimaryKey();
            }
            throw new \RuntimeException('Unknown relation foreign key');
        }
        return $this->foreignKey;
    }

    /**
     * @return int|string
     */
    public function getLocalKey()
    {
        if($this->localKey === null){
            throw new \RuntimeException('Unknown relation foreign key');
        }
        return $this->localKey;
    }

    /**
     * @return ResultCollection|Result|ModelInterface
     */
    public function getResults()
    {
        return $this->results === null ? new ResultCollection() : $this->results;
    }

    /**
     * @param Result|ResultCollection|ModelInterface $results
     * @return $this
     */
    public function setResults($results)
    {
        if(!($results instanceof Result || $results instanceof ResultCollection || $results instanceof ModelInterface)){
            throw new \InvalidArgumentException('Invalid result set for relation');
        }

        $this->results = $results;
        return $this;
    }
}