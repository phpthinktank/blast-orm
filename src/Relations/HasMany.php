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

use Blast\Orm\ConnectionAwareInterface;
use Blast\Orm\ConnectionAwareTrait;
use Blast\Orm\Entity\Provider;
use Blast\Orm\Entity\ProviderFactoryInterface;
use Blast\Orm\Entity\ProviderFactoryTrait;
use Blast\Orm\Hydrator\HydratorInterface;
use Blast\Orm\Query;

class HasMany implements ConnectionAwareInterface, ProviderFactoryInterface, RelationInterface
{

    use ConnectionAwareTrait;
    use ProviderFactoryTrait;
    use RelationTrait;

    /**
     * @var
     */
    private $entity;
    /**
     * @var
     */
    private $foreignEntity;
    /**
     * @var null
     */
    private $foreignKey;

    /**
     * Local entity relates to many entries of foreign entity by foreign key
     *
     * @param $entity
     * @param $foreignEntity
     * @param null $foreignKey
     */
    public function __construct($entity, $foreignEntity, $foreignKey = null)
    {
        $this->entity = $entity;
        $this->foreignEntity = $foreignEntity;
        $this->foreignKey = $foreignKey;
    }

    /**
     * @return \\ArrayObject
     */
    public function execute()
    {
        return $this->getQuery()->execute(HydratorInterface::HYDRATE_COLLECTION);
    }

    protected function init()
    {
        $provider = $this->createProvider($this->getEntity());
        $foreignProvider = $this->createProvider($this->getForeignEntity());
        $foreignKey = $this->getForeignKey();

        $data = $provider->fromObjectToArray();

        //find primary key
        if ($foreignKey === null) {
            $foreignKey = $provider->getTableName() . '_' . $provider->getPrimaryKeyName();
        }

        $mapper = $foreignProvider->getMapper()->setConnection($this->getConnection());

        $foreignKeyValue = isset($data[$provider->getPrimaryKeyName()]) ? $data[$provider->getPrimaryKeyName()] : false;

        //if no primary key is available, return a select
        $query = $mapper->select();
        if ($foreignKeyValue !== false) {
            $query->where((new Query($this->getConnection()))->expr()->eq($foreignKey, $foreignKeyValue));
        }
        $this->query = $query;
        $this->name = $foreignProvider->getTableName();

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return mixed
     */
    public function getForeignEntity()
    {
        return $this->foreignEntity;
    }

    /**
     * @return null
     */
    public function getForeignKey()
    {
        return $this->foreignKey;
    }


}
