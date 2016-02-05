<?php
/*
*
* (c) Marco Bunge <marco_bunge@web.de>
*
* For the full copyright and license information, please view the LICENSE.txt
* file that was distributed with this source code.
*
* Date: 05.02.2016
* Time: 13:45
*/

namespace Blast\Db\Orm\Traits;


use Blast\Db\Orm\Factory;

trait ConnectionAwareTrait
{

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection = NULL;

    /**
     * Get connection.
     *
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection()
    {
        if ($this->connection === null) {
            $this->connection = Factory::getInstance()->getConfig()->getConnection();
        }
        return $this->connection;
    }

}