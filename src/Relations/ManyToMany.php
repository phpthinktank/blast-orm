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


use Blast\Orm\Entity\EntityAdapter;
use Blast\Orm\Entity\EntityAdapterLoaderTrait;
use Blast\Orm\Query;

class ManyToMany implements RelationInterface
{
    use EntityAdapterLoaderTrait;
    use RelationTrait;

    /**
     * Local entity relates to many entries of foreign entity by foreign key
     *
     * @param $entity
     * @param $foreignEntity
     * @param null $foreignKey
     */
    public function __construct($entity, $foreignEntity, $foreignKey = null, $localKey = null, $pivot = null, $pivotLocalKey = null, $pivotForeignKey = null)
    {
        $adapter = $this->loadAdapter($entity);
        $foreignAdapter = $this->loadAdapter($foreignEntity);

        $data = $adapter->getData();

        $localKey = $adapter->getPrimaryKeyName();

        if($foreignKey === null){
            $foreignKey = $foreignAdapter->getTableName() . '_' . $foreignAdapter->getPrimaryKeyName();
        }

        $mapper = $foreignAdapter->getMapper();

        $query = new Query();

        //if no primary key is available, return a select

        $result = $query->select([$pivotForeignKey])->where($query->expr()->eq($pivotLocalKey, $data[$localKey]))->execute(EntityAdapter::RESULT_RAW);

        $foreignQuery = new Query($foreignEntity);
        $foreignQuery->select([$pivotForeignKey]);

        foreach($result as $value){
            $foreignQuery->where($query->expr()->eq($foreignKey, $value[$foreignKey]));
        }

        $this->query =  $foreignQuery;
        $this->name = $foreignAdapter->getTableName();
    }
}
