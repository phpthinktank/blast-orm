<?php
/*
*
* (c) Marco Bunge <marco_bunge@web.de>
*
* For the full copyright and license information, please view the LICENSE.txt
* file that was distributed with this source code.
*
* Date: 18.02.2016
* Time: 21:18
*/

namespace Blast\Db\Orm\Model;


interface TableAwareInterface
{
    /**
     * Get table for model
     *
     * @return string
     */
    public function getTable();
}