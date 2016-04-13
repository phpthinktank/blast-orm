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
     * @param $data
     * @param \Doctrine\DBAL\Schema\Column[] $fields
     * 
     * @return $this
     */
    public function insert($data, $fields = []);

    /**
     * Prepare update statement
     *
     * @param $primaryKeyName
     * @param $data
     * @param \Doctrine\DBAL\Schema\Column[] $fields
     * 
     * @return mixed
     */
    public function update($primaryKeyName, $data, $fields = []);

    /**
     * Prepare delete statement
     *
     * @param $primaryKeyName
     * @param $primaryKey
     * 
     * @return mixed
     */
    public function delete($primaryKeyName, $primaryKey);
    
}
