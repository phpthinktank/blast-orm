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


use Blast\Orm\ConnectionAwareInterface;
use Blast\Orm\ConnectionAwareTrait;
use Blast\Orm\Entity\Definition;
use Blast\Orm\Entity\ProviderFactoryInterface;
use Blast\Orm\Entity\ProviderFactoryTrait;
use Blast\Orm\Hydrator\HydratorInterface;
use Blast\Orm\Query;
use Doctrine\Common\Util\Inflector;

class ManyToMany implements ConnectionAwareInterface, ProviderFactoryInterface, RelationInterface
{
    use ConnectionAwareTrait;
    use ProviderFactoryTrait;
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
     * @param null|string $foreignKey Default field name is {foreign primary key name}
     * @param null|string $localKey Default field name is {local primary key name}
     * @param null|string|object $junction Default table name is {local entity table name}_{foreign entity table name}
     * @param null|string $junctionLocalKey Default field name is {local table name}_{$localKey}
     * @param null|string $junctionForeignKey Default field name is {foreign table name}_{$foreignKey}
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
     * @return null|string
     */
    public function getLocalKey()
    {
        return $this->localKey;
    }

    /**
     * @return \SplStack
     */
    public function execute()
    {
        return $this->getQuery()->execute(HydratorInterface::HYDRATE_COLLECTION);
    }

    /**
     * Get relation query
     *
     * @return \Blast\Orm\Query
     */
    public function getQuery()
    {
        if (null !== $this->query) {
            return $this->query;
        }
        $provider = $this->createProvider($this->getEntity());
        $foreignProvider = $this->createProvider($this->getForeignEntity());
        $foreignKey = $this->getForeignKey();
        $junction = $this->getJunction();
        $junctionLocalKey = $this->getJunctionLocalKey();
        $junctionForeignKey = $this->getJunctionForeignKey();

        $data = $provider->fetchData();

        $localKey = $provider->getDefinition()->getPrimaryKeyName();

        //determine foreign key
        if ($foreignKey === null) {
            $foreignKey = $foreignProvider->getDefinition()->getPrimaryKeyName();
        }

        //determine through
        if (!is_string($junction) || $junction === null) {
            $junction = Inflector::singularize($provider->getDefinition()->getTableName()) . '_' . Inflector::singularize($foreignProvider->getDefinition()->getTableName());
        }

        //determine through local key
        if ($junctionLocalKey === null) {
            $junctionLocalKey = Inflector::singularize($provider->getDefinition()->getTableName()) . '_' . $localKey;
        }

        //determine through foreign key
        if ($junctionForeignKey === null) {
            $junctionForeignKey = Inflector::singularize($foreignProvider->getDefinition()->getTableName()) . '_' . $foreignKey;
        }

        $query = new Query($provider->getDefinition()->getMapper()->getConnection());

        //prepare query for foreign table
        $foreignQuery = $foreignProvider->getDefinition()->getMapper()
            ->setConnection($this->getConnection())
            ->select();

        //get relations by through db object
        if (isset($data[$localKey])) {
            $junctionProvider = is_string($junction) ? 
                $this->createProvider($junction) : 
                $junction;
            $junctionMapper = $junctionProvider->getDefinition()->getMapper();
            $junctionMapper->setConnection($this->getConnection());
            if(true){

            }
            $results = $junctionMapper
                ->select([$junctionForeignKey])
                ->where($query->expr()->eq($junctionLocalKey, $data[$localKey]))
                ->execute(HydratorInterface::HYDRATE_RAW);

            //set conditions on foreign query
            foreach ($results as $result) {
                $foreignQuery->where($query->expr()->eq($foreignKey, $result[$junctionForeignKey]));
            }
        }

        $this->query = $foreignQuery;

        return $this->query;
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
}
