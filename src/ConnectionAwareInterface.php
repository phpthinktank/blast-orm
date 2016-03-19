<?php
/*
*
* (c) Marco Bunge <marco_bunge@web.de>
*
* For the full copyright and license information, please view the LICENSE.txt
* file that was distributed with this source code.
*
* Date: 15.03.2016
* Time: 20:33
*/

namespace Blast\Orm;


interface ConnectionAwareInterface
{
    /**
     * Get current connection
     *
     * @return \Doctrine\DBAL\Driver\Connection|\Doctrine\DBAL\Connection
     */
    public function getConnection();

    /**
     * @param \Doctrine\DBAL\Driver\Connection|null $connection
     *
     * @return $this
     */
    public function setConnection($connection);
}
