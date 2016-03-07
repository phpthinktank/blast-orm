<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 29.02.2016
 * Time: 16:45
 *
 */

namespace Blast\Orm\Relations;

use Blast\Orm\Entity\EntityAdapterInterface;
use Blast\Orm\Entity\EntityAdapterLoaderTrait;
use Blast\Orm\Entity\EntityHydratorInterface;
use Blast\Orm\Entity\Definition;
use Blast\Orm\Query;

class ManyToMany implements RelationInterface
{
    use EntityAdapterLoaderTrait;
    use RelationTrait;
    /**
     * @var object|string
     */
    private $entity;
    /**
     * @var object|string
     */
    private $foreignEntity;
    /**
     * @var null|string
     */
    private $foreignKey;
    /**
     * @var null|string
     */
    private $localKey;
    /**
     * @var null|object|string
     */
    private $junction;
    /**
     * @var null|string
     */
    private $junctionLocalKey;
    /**
     * @var null|string
     */
    private $junctionForeignKey;

    /**
     * Many occurrences in local entity relate to many occurrences in foreign entity and vice versa.
     * The relations are linked by a junction table.
     *
     * @param string|object $entity
     * @param string|object $foreignEntity
     * @param null|string $foreignKey          Default field name is {foreign primary key name}
     * @param null|string $localKey            Default field name is {local primary key name}
     * @param null|string|object $junction     Default table name is {local entity table name}_{foreign entity table name}
     * @param null|string $junctionLocalKey    Default field name is {local table name}_{$localKey}
     * @param null|string $junctionForeignKey  Default field name is {foreign table name}_{$foreignKey}
     */
    public function __construct($entity, $foreignEntity, $foreignKey = null, $localKey = null,
                                $junction = null, $junctionLocalKey = null, $junctionForeignKey = null)
    {

        $this->entity = $entity;
        $this->foreignEntity = $foreignEntity;
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
        $this->junction = $junction;
        $this->junctionLocalKey = $junctionLocalKey;
        $this->junctionForeignKey = $junctionForeignKey;
    }

    /**
     * @return object|string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return object|string
     */
    public function getForeignEntity()
    {
        return $this->foreignEntity;
    }

    /**
     * @return null|string
     */
    public function getForeignKey()
    {
        return $this->foreignKey;
    }

    /**
     * @return null|string
     */
    public function getLocalKey()
    {
        return $this->localKey;
    }

    /**
     * @return null|object|string
     */
    public function getJunction()
    {
        return $this->junction;
    }

    /**
     * @return null|string
     */
    public function getJunctionLocalKey()
    {
        return $this->junctionLocalKey;
    }

    /**
     * @return null|string
     */
    public function getJunctionForeignKey()
    {
        return $this->junctionForeignKey;
    }

    protected function init(){
        $adapter = $this->loadAdapter($this->getEntity());
        $foreignAdapter = $this->loadAdapter($this->getForeignEntity());
        $foreignKey = $this->getForeignKey();
        $junction = $this->getJunction();
        $junctionLocalKey = $this->getJunctionLocalKey();
        $junctionForeignKey = $this->getJunctionForeignKey();

        $data = $adapter->getData();

        $localKey = $adapter->getPrimaryKeyName();

        //determine foreign key
        if ($foreignKey === null) {
            $foreignKey = $foreignAdapter->getPrimaryKeyName();
        }

        //determine through
        if (!is_string($junction) || $junction === null) {
            $junction = $adapter->getTableName() . '_' . $foreignAdapter->getTableName();
        }

        //determine through local key
        if($junctionLocalKey === null){
            $junctionLocalKey = $adapter->getTableName() . '_' . $localKey;
        }

        //determine through foreign key
        if($junctionForeignKey === null){
            $junctionForeignKey = $foreignAdapter->getTableName() . '_' . $foreignKey;
        }

        $query = new Query();

        //get relations by through db object
        if(isset($data[$localKey])){
            $junctionAdapter = $this->loadAdapter(is_string($junction) ? new Definition($junction) : $junction);
            $results = $junctionAdapter->getMapper()
                ->select([$junctionForeignKey])
                ->where($query->expr()->eq($junctionLocalKey, $data[$localKey]))
                ->execute(EntityAdapterInterface::HYDRATE_RAW);

            $foreignQuery = $foreignAdapter->getMapper()->select();

            foreach ($results as $result) {
                $foreignQuery->where($query->expr()->eq($foreignKey, $result[$junctionForeignKey]));
            }

        }else{
            $foreignQuery = $foreignAdapter->getMapper()->select();
        }

        $this->query = $foreignQuery;
        $this->name = $foreignAdapter->getTableName();
    }

    /**
     * @return \Blast\Orm\Data\DataObject
     */
    public function execute(){
        return $this->getQuery()->execute(EntityHydratorInterface::HYDRATE_COLLECTION);
    }
}
