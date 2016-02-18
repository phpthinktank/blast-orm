<?php
/*
*
* (c) Marco Bunge <marco_bunge@web.de>
*
* For the full copyright and license information, please view the LICENSE.txt
* file that was distributed with this source code.
*
* Date: 18.02.2016
* Time: 21:19
*/

namespace Blast\Db\Orm\Model;


interface PrimaryKeyAwareTrait
{
    /**
     * Get primary key
     *
     * @return string
     */
    public function getPrimaryKey();
}