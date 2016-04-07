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


use Blast\Orm\ConnectionAwareInterface;
use Blast\Orm\ConnectionAwareTrait;
use Blast\Orm\Entity\ProviderFactoryInterface;
use Blast\Orm\Entity\ProviderFactoryTrait;
use Blast\Orm\Hydrator\HydratorInterface;
use Blast\Orm\Query;
use Doctrine\Common\Inflector\Inflector;

class BelongsTo implements ConnectionAwareInterface, RelationInterface, ProviderFactoryInterface
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
    private $localKey;

    /**
     * Local entity belongs to foreign entity by local key
     *
     * @param $entity
     * @param $foreignEntity
     * @param null $localKey
     */
    public function __construct($entity, $foreignEntity, $localKey = null)
    {
        $this->entity = $entity;
        $this->foreignEntity = $foreignEntity;
        $this->localKey = $localKey;
    }

    /**
     * @return array|\ArrayObject|object|bool
     */
    public function execute()
    {
        return $this->getQuery()->execute(HydratorInterface::HYDRATE_AUTO);
    }

    /**
     * Get relation query
     *
     * @return \Blast\Orm\Query
     */
    public function getQuery()
    {
        if(null !== $this->query){
            return $this->query;
        }
        $provider = $this->createProvider($this->getEntity());
        $foreignProvider = $this->createProvider($this->getForeignEntity());
        $localKey = $this->getLocalKey();

        if ($localKey === null) {
            $localKey = Inflector::singularize($foreignProvider->getDefinition()->getTableName()) . '_' . $foreignProvider->getDefinition()->getPrimaryKeyName();
        }

        $data = $provider->fetchData();

        //find primary key
        $primaryKey = $data[$localKey];

        $mapper = $foreignProvider->getDefinition()->getMapper()->setConnection($this->getConnection());

        //if no primary key is available, return a select
        $this->query = $primaryKey === null ? $mapper->select() : $mapper->find($primaryKey);

        return $this->query;
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
    public function getLocalKey()
    {
        return $this->localKey;
    }

}
