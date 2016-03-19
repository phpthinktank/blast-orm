<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 25.02.2016
 * Time: 13:21
 *
 */

namespace Blast\Orm\Query\Events;


use League\Event\AbstractEvent;

abstract class AbstractQueryEvent extends AbstractEvent
{

    private $canceled = false;

    /**
     * Check if execution of query is canceled
     * @return boolean
     */
    public function isCanceled()
    {
        return $this->canceled;
    }

    /**
     * @param boolean $canceled
     */
    public function setCanceled($canceled)
    {
        $this->canceled = $canceled;
    }

}
