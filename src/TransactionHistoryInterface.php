<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 18.03.2016
 * Time: 07:50
 *
 */

namespace Blast\Orm;

/**
 *
 * The transaction history tracks transaction status for an entity by statement type
 *
 */
interface TransactionHistoryInterface
{

    const PENDING = 1;
    const PROCESS = 2;
    const COMPLETE = 4;

    public function store($id, $entity, $type, $status = self::PENDING, $data = null);

}
