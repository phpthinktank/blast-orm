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

namespace Blast\Db\Orm\Traits;


use Blast\Db\Orm\Factory;

trait FactoryAwareTrait
{

    /**
     * @var Factory
     */
    private $factory = NULL;

    /**
     * @return Factory
     */
    public function getFactory()
    {
        if ($this->factory === null) {
            $this->factory = Factory::getInstance();
        }
        return $this->factory;
    }

}