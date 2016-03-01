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
use Blast\Orm\Entity\GenericEntity;
use Blast\Orm\Query;

class ManyToMany implements RelationInterface
{
    use EntityAdapterLoaderTrait;
    use RelationTrait;

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
        $adapter = $this->loadAdapter($entity);
        $foreignAdapter = $this->loadAdapter($foreignEntity);

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

        $throughAdapter = $this->loadAdapter(is_string($junction) ? new GenericEntity($junction) : $junction);

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
        $results = $throughAdapter->getMapper()
            ->select([$junctionForeignKey])
            ->where($query->expr()->eq($junctionLocalKey, $data[$localKey]))
            ->execute(EntityAdapterInterface::HYDRATE_RAW);

        $foreignQuery = $foreignAdapter->getMapper()->select([$junctionForeignKey]);

        foreach ($results as $result) {
            $foreignQuery->where($query->expr()->eq($foreignKey, $result[$junctionForeignKey]));
        }

        $this->query = $foreignQuery;
        $this->name = $foreignAdapter->getTableName();
    }
}