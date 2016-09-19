<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 13.04.2016
 * Time: 17:08
 *
 */

namespace Blast\Orm;

/**
 * The gateway is accessing a single table or view and interacts with database
 *
 * @package Blast\Orm
 */
interface GatewayInterface
{

    /**
     * Create a new gateway for a single table
     *
     * @param $table
     */
    public function __construct($table);

    /**
     * Prepare insert statement
     *
     * @param array $primaryKeyNames list of primary key or primary composite key
     * @param $data
     * @param \Doctrine\DBAL\Schema\Column[] $fields
     * @return Query
     */
    public function insert(array $primaryKeyNames, array $data, $fields = []);

    /**
     * Prepare update statement
     *
     * @param array $primaryKeyNames Primary Key or primary composite key as keyName => value
     * @param $data
     * @param \Doctrine\DBAL\Schema\Column[] $fields
     *
     * @return Query
     */
    public function update(array $primaryKeyNames, array $data, $fields = []);

    /**
     * Prepare delete statement
     *
     * @param array $primaryKeys Primary Key or primary composite key as keyName => value
     *
     * @return Query
     */
    public function delete(array $primaryKeys);

}
