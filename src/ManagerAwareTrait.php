<?php
/*
*
* (c) Marco Bunge <marco_bunge@web.de>
*
* For the full copyright and license information, please view the LICENSE.txt
* file that was distributed with this source code.
*
* Date: 05.02.2016
* Time: 13:42
*/

namespace Blast\Db;

trait ManagerAwareTrait
{

    /**
     * @var Manager
     */
    private $factory = NULL;

    /**
     * @return Manager
     */
    public function getFactory()
    {
        if ($this->factory === null) {
            $this->factory = Manager::getInstance();
        }
        return $this->factory;
    }

}