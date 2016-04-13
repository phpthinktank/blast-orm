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
use Doctrine\DBAL\Schema\Column;
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
     * @param Column[] $fields
     * @return $this
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

        foreach ($data as $key => $value) {
            if ($value instanceof RelationInterface) {
                continue;
            };

            $query->setValue($key, $query->createPositionalParameter(
                $value, array_key_exists($key, $fields) ?
                $fields[$key]->getType()->getName() :
                Type::STRING));
        }

        return $query;
    }

    /**
     * Prepare update statement
     *
     * @param $primaryKey
     * @param $data
     *
     * @return mixed
     */
    public function update($primaryKey, $data)
    {
        // TODO: Implement update() method.
    }

    /**
     * Prepare delete statement
     *
     * @param $primaryKey
     *
     * @return mixed
     */
    public function delete($primaryKey)
    {
        // TODO: Implement delete() method.
    }
}
