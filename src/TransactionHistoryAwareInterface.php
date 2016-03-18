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


interface TransactionHistoryAwareInterface
{
    /**
     * Get transaction history
     *
     * @return \Blast\Orm\TransactionHistoryInterface
     */
    public function getTransactionHistory();

    /**
     * Add transaction history
     *
     * @param \Blast\Orm\TransactionHistoryInterface $transactionHistory
     * @return $this
     */
    public function setTransactionHistory(TransactionHistoryInterface $transactionHistory);
}
