<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 29.02.2016
 * Time: 12:14
 *
 */

namespace Blast\Orm\Relations;


use Blast\Orm\Entity\EntityAdapterLoaderTrait;
use Blast\Orm\Query;

class HasMany implements RelationInterface
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
    public function __construct($entity, $foreignEntity, $foreignKey = null)
    {
        $adapter = $this->loadAdapter($entity);
        $foreignAdapter = $this->loadAdapter($foreignEntity);

        $data = $adapter->getData();

        //find primary key
        $foreignKeyValue = $data[$adapter->getPrimaryKeyName()];
        if($foreignKey === null){
            $foreignKey = $adapter->getTableName() . '_' . $adapter->getPrimaryKeyName();
        }

        $mapper = $foreignAdapter->getMapper();

        //if no primary key is available, return a select

        $this->query = $mapper->select()->where((new Query())->expr()->eq($foreignKey, $foreignKeyValue));

//        $this->query = $adapter->getMapper()->find($foreignAdapter->access($foreignKey, $adapter->getTableName() . '_' . $adapter->getPrimaryKeyName()));
        $this->name = $foreignAdapter->getTableName();
    }

}