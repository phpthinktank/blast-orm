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


use Doctrine\DBAL\Connection as DbalConnection;
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
    
    use MapperFactoryTrait {
        createMapper as protected internalCreateMapper;
    }

    /**
     * Create a new Mapper for given entity.
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
     * Create a new query for given entity with optional custom query builder.
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

}
