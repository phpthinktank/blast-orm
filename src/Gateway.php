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


use Blast\Orm\Relations\RelationInterface;
use Doctrine\DBAL\Types\Type;

class Gateway implements GatewayInterface, ConnectionAwareInterface
{

    use ConnectionAwareTrait;

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
     * @param $data
     *
     * @param \Doctrine\DBAL\Schema\Column[] $fields
     * @return Query|bool
     */
    public function insert($data, $fields = [])
    {
        //cancel if $data has no entries
        if (count($data) < 1) {
            return false;
        }

        //prepare statement
        $query = $this->getConnection()->createQuery();
        $query->insert($this->table);
        $this->addDataToQuery($data, $fields, $query);

        return $query;
    }

    /**
     * Prepare update statement
     *
     * @param $primaryKeyName
     * @param $data
     * @param \Doctrine\DBAL\Schema\Column[] $fields
     *
     * @return mixed
     */
    public function update($primaryKeyName, $data, $fields = [])
    {
        //prepare statement
        $query = $this->getConnection()->createQuery();
        $query->update($this->table);

        $this->addDataToQuery($data, $fields, $query);

        return $query->where($query->expr()->eq($primaryKeyName, $data[$primaryKeyName]));
    }

    /**
     * Prepare delete statement
     *
     * @param $primaryKeyName
     * @param $primaryKey
     *
     * @return mixed
     */
    public function delete($primaryKeyName, $primaryKey)
    {
        $query = $this->getConnection()->createQuery();
        $query
            ->delete($this->table)
            ->where($query->expr()->eq($primaryKeyName, $query->createPositionalParameter($primaryKey)));

        return $query;

    }

    /**
     *
     * @todo determine exclusion from gateway and integration into query similar to php value convert
     *
     * @param $data
     * @param \Doctrine\DBAL\Schema\Column[] $fields
     * @param Query $query
     */
    protected function addDataToQuery($data, $fields, Query $query)
    {
        foreach ($data as $key => $value) {
            $query->addColumnValue($key, $query->createPositionalParameter(
                $value, array_key_exists($key, $fields) ?
                $fields[$key]->getType()->getName() :
                Type::STRING));
        }
    }
}
