<?php
/*
*
* (c) Marco Bunge <marco_bunge@web.de>
*
* For the full copyright and license information, please view the LICENSE.txt
* file that was distributed with this source code.
*
* Date: 05.02.2016
* Time: 13:50
*/

namespace Blast\Db\Entity\Traits;


use Blast\Db\Entity\Manager;
use Blast\Db\Entity\ManagerInterface;
use Blast\Db\Factory;

trait EntityAwareTrait
{
    /**
     * @var Manager
     */
    private $manager;

    /**
     * @return Manager
     */
    public function getManager()
    {
        if ($this->manager === null) {
            $managerConcrete = Factory::getInstance()->getContainer()->get(ManagerInterface::class);
            $this->manager = (new \ReflectionClass($managerConcrete))->newInstanceArgs([$this->getEntity(), $this, $this->getFactory()]);
        }
        return $this->manager;
    }
}