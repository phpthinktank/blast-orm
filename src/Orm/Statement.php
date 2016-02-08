<?php
/*
*
* (c) Marco Bunge <marco_bunge@web.de>
*
* For the full copyright and license information, please view the LICENSE.txt
* file that was distributed with this source code.
*
* Date: 08.02.2016
* Time: 16:11
*/

namespace Blast\Db\Orm;


use Blast\Db\Entity\Collection;
use Blast\Db\Entity\CollectionInterface;
use Blast\Db\Entity\EntityInterface;
use Blast\Db\Entity\ManagerInterface;
use Blast\Db\Orm\Traits\ConnectionAwareTrait;
use Blast\Db\FactoryAwareTrait;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Class Statement
 * @package Blast\Db\Orm
 */
class Statement
{

    use FactoryAwareTrait;
    use ConnectionAwareTrait;

    /**
     *
     */
    const RESULT_COLLECTION = 'collection';
    /**
     *
     */
    const RESULT_ENTITY = 'entity';
    /**
     *
     */
    const RESULT_AUTO = 'auto';

    /**
     * @var QueryBuilder
     */
    private $builder;
    /**
     * @var ManagerInterface
     */
    private $manager;

    /**
     * Statement constructor.
     * @param QueryBuilder $builder
     * @param ManagerInterface $manager
     */
    public function __construct(QueryBuilder $builder, ManagerInterface $manager = null)
    {
        $this->builder = $builder;
        $this->manager = $manager;
    }

    /**
     * @return QueryBuilder
     */
    public function getBuilder()
    {
        return $this->builder;
    }

    /**
     * @return ManagerInterface
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Fetch data for entity. if raw is true, fetch assoc instead of entity
     *
     * @param string $convert
     * @param bool $raw
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function fetch($convert = self::RESULT_AUTO, $raw = FALSE)
    {
        $builder = $this->getBuilder();
        $result = $this->getConnection()->executeQuery($builder->getSQL(), $builder->getParameters())->fetchAll();

        return $raw === TRUE || $this->getManager() === null ? $result : $this->determineResultSet($result, $convert);
    }

    /**
     * Analyse result and return one or many results
     *
     * @param $data
     * @param string $convert
     * @return CollectionInterface|EntityInterface|null
     */
    protected function determineResultSet($data, $convert = self::RESULT_AUTO)
    {
        $count = count($data);
        $result = NULL;

        if ($count > 1 || $convert === static::RESULT_COLLECTION) { //if result set has many items, return a collection of entities
            foreach($data as $key => $value){
                $data[$key] = $this->getManager()->create()->setData($value);
            }
            $result = new Collection($data);
        } elseif ($count === 1 || $convert === static::RESULT_ENTITY) { //if result has one item, return the entity
            $result = $this->getManager()->create()->setData(array_shift($data));
        }

        return $result;
    }

}