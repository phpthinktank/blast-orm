<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 29.02.2016
 * Time: 16:37
 *
 */

namespace Blast\Orm\Relations;


use Blast\Orm\Hydrator\HydratorInterface;

class HasOne extends HasMany
{
    /**
     * @return \ArrayObject|object
     */
    public function execute()
    {
        return $this->getQuery()->execute(HydratorInterface::HYDRATE_ENTITY);
    }

    protected function init()
    {
        parent::init();
        $this->query->setMaxResults(1);
    }

}
