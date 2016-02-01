<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 01.02.2016
 * Time: 11:25
 *
 */

namespace Blast\Db\Schema;


use Doctrine\DBAL\Schema\Table as DbalTable;

class Table extends DbalTable
{

    /**
     * get types of all columns
     * @return array
     */
    public function getColumnsTypes(){
        $columns = $this->getColumns();
        $types = [];

        foreach ($columns as $column) {
            $types[$column->getName()] = $column->getType();
        }

        return $types;

    }

    /**
     * Returns first primary key!
     * @return string
     */
    public function getPrimaryKeyName(){
        return array_shift($this->getPrimaryKey()->getColumns());
    }

}