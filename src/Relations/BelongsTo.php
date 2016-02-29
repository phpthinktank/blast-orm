<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 29.02.2016
 * Time: 12:04
 *
 */

namespace Blast\Orm\Relations;


use Blast\Orm\Entity\EntityAdapterLoaderTrait;
use Blast\Orm\Query;

class BelongsTo implements RelationInterface
{
    use EntityAdapterLoaderTrait;
    use RelationTrait;

    /**
     * Local entity belongs to foreign entity by local key
     *
     * @param $entity
     * @param $foreignEntity
     * @param null $localKey
     */
    public function __construct($entity, $foreignEntity, $localKey = null)
    {
        $adapter = $this->loadAdapter($entity);
        $foreignAdapter = $this->loadAdapter($foreignEntity);

        if($localKey === null){
            $localKey = $foreignAdapter->getTableName() . '_' . $foreignAdapter->getPrimaryKeyName();
        }

        $data = $adapter->getData();

        //find primary key
        $primaryKey = null;

        if(isset($data[$localKey])){
            $primaryKey = $data[$localKey];
        }else{
            $primaryKey = $adapter->access($localKey);
        }

        $mapper = $foreignAdapter->getMapper();

        //if no primary key is available, return a select
        $this->query = $primaryKey === null ? $mapper->select() : $mapper->find($primaryKey);
        $this->name = $foreignAdapter->getTableName();

    }

}