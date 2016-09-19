<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 13.04.2016
 * Time: 17:23
 *
 */

namespace Blast\Orm;


use Blast\Orm\Locator\LocatorAwareInterface;
use Blast\Orm\Locator\LocatorAwareTrait;
use Doctrine\Common\Collections\ExpressionBuilder;
use Doctrine\DBAL\Types\Type;
use SebastianBergmann\GlobalState\RuntimeException;

class Gateway implements GatewayInterface, LocatorAwareInterface
{

    use LocatorAwareTrait;

    /**
     * @var string
     */
    private $table;

    /**
     * Create a new gateway for a single table
     *
     * @param $table
     */
    public function __construct($table)
    {
        $this->table = $table;
    }

    /**
     * Prepare insert statement
     *
     * @param array $primaryKeyNames list of primary key or primary composite key
     * @param array $data
     * @param \Doctrine\DBAL\Schema\Column[] $fields
     * @param array $conditions
     * @return Query
     * @throws \Exception
     */
    public function insert(array $primaryKeyNames, array $data, $fields = [], array $conditions = [])
    {
        //cancel if $data has no entries
        if (count($data) < 1) {
            throw new \Exception('No data!');
        }

        //prepare statement
        /** @var Query $query */
        $query = $this->getLocator()->get(Query::class);
        $query->insert($this->table);
        $this->addDataToQuery($data, $fields, $query);

        $expression = $query->expr();

        foreach ($primaryKeyNames as $name){
            if(!isset($data[$name])){
                throw new \RuntimeException('Invalid value for primary key ' . $name);
            }

            $conditions[] = $expression->eq($name, $query->createPositionalParameter($data[$name]));
        }

        return $query->where(call_user_func_array([$expression, 'andX'], $conditions));
    }

    /**
     * Prepare update statement
     *
     * @param array $primaryKeyNames Primary Key or primary composite key as keyName => value
     * @param array $data
     * @param \Doctrine\DBAL\Schema\Column[] $fields
     * @param array $conditions
     *
     * @return Query
     */
    public function update(array $primaryKeyNames, array $data, $fields = [], array $conditions = [])
    {
        //prepare statement
        /** @var Query $query */
        $query = $this->getLocator()->get(Query::class);
        $query->update($this->table);

        $this->addDataToQuery($data, $fields, $query);

        $expression = $query->expr();

        foreach ($primaryKeyNames as $name){
            if(!isset($data[$name])){
                throw new \RuntimeException('Invalid value for primary key ' . $name);
            }

            $conditions[] = $expression->eq($name, $query->createPositionalParameter($data[$name]));
        }

        return $query->where(call_user_func_array([$expression, 'andX'], $conditions));
    }

    /**
     * Prepare delete statement
     *
     * @param array $primaryKeys Primary Key or primary composite key as keyName => value
     *
     * @param array $conditions
     * @return Query
     */
    public function delete(array $primaryKeys, array $conditions = [])
    {
        /** @var Query $query */
        $query = $this->getLocator()->get(Query::class);
        $query->delete($this->table);

        $expression = $query->expr();

        foreach ($primaryKeys as $name => $value){
            $conditions[] = $expression->eq($name, $query->createPositionalParameter($value));
        }

        return $query->where(call_user_func_array([$expression, 'andX'], $conditions));

    }

    /**
     * Add data to query
     *
     * @param $data
     * @param \Doctrine\DBAL\Schema\Column[] $fields
     * @param Query $query
     * @throws \Exception
     */
    protected function addDataToQuery($data, $fields, Query $query)
    {
        foreach ($data as $key => $value) {

            $type = isset($fields[$key]) ? $fields[$key] : Type::STRING;


            if (is_string($type)) {
                $type = Type::getType($type);
            }

            if (!($type instanceof Type)) {
                throw new \Exception('Type needs to be a valid type or type instance');
            }

            $query->addColumnValue($key, $query->createPositionalParameter($value, $type));
        }
    }
}
