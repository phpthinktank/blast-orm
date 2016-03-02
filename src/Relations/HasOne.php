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


class HasOne extends HasMany
{
    protected function init()
    {
        parent::init();
        $this->query->setMaxResults(1);
    }

}