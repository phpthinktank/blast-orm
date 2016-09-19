<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 13.04.2016
 * Time: 08:20
 *
 */

namespace Blast\Orm;


use Blast\Orm\Query\QueryFactoryInterface;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * The connection wrapper provides connection-aware mapper and query based on current connection
 *
 *
 * @package Blast\Orm
 */
class Connection extends DbalConnection implements MapperFactoryInterface, QueryFactoryInterface
{

    /**
     * Table name prefix for connections
     * @var null|string
     */
    private $prefix = null;

    /**
     *
     * @var null|\Doctrine\Common\Cache\Cache
     */
    private $internalCache = null;

    /**
     *
     * @var null|\Doctrine\Common\Cache\Cache
     */
    private $metaDataCache = null;


    /**
     *
     * @var null|\Doctrine\Common\Cache\Cache
     */
    private $reflectionCache = null;

    use MapperFactoryTrait {
        createMapper as protected internalCreateMapper;
    }

    public function __construct(array $params, Driver $driver, $config, $eventManager)
    {
        parent::__construct($params, $driver, $config, $eventManager);
    }


    /**
     * Factory method for create a new Mapper for given entity.
     *
     *  * ```php
     *
     * //create mapper from connection
     * $connection->createMapper(Post::class);
     *
     * ```
     *
     * @param $entity
     *
     * @return Mapper
     */
    public function createMapper($entity){
        return $this->internalCreateMapper($entity, $this);
    }

    /**
     * Factory method for create a new query for given entity with optional custom query builder.
     *
     * @param $entity
     *
     * @param \Doctrine\DBAL\Query\QueryBuilder $builder
     *
     * @return \Blast\Orm\Query
     */
    public function createQuery($entity = null, QueryBuilder $builder = null)
    {
        $query = new Query($this, $entity);
        $query->setBuilder(null === $builder ? parent::createQueryBuilder() : $builder);
        return $query;
    }

    /**
     * @return null|string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param null|string $prefix
     * @return $this
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    private function getInternalCache()
    {
        if (null === $this->internalCache) {
            $this->internalCache = new ArrayCache();
        }

        return $this->internalCache;
    }

    /**
     * @return Cache|null
     */
    public function getMetaDataCache()
    {
        if(null === $this->metaDataCache){
            $cache = $this->getInternalCache();
            $this->metaDataCache = clone $cache;
        }
        return $this->metaDataCache;
    }

    /**
     * @return Cache|null
     */
    public function getReflectionCache()
    {
        if(null === $this->reflectionCache){
            $cache = $this->getInternalCache();
            $this->reflectionCache = clone $cache;
        }
        return $this->reflectionCache;
    }

}
