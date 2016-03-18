<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 18.03.2016
 * Time: 07:49
 *
 */

namespace Blast\Orm;


trait TransactionHistoryAwareTrait
{
    /**
     * @var \Blast\Orm\TransactionHistoryInterface
     */
    private $transactionHistory;

    /**
     * Get transaction history
     *
     * @return \Blast\Orm\TransactionHistory
     */
    public function getTransactionHistory()
    {
        if(null === $this->transactionHistory){
            $this->transactionHistory = new TransactionHistory();
        }
        return $this->transactionHistory;
    }

    /**
     * Add transaction history
     *
     * @param \Blast\Orm\TransactionHistoryInterface $transactionHistory
     * @return $this
     */
    public function setTransactionHistory(TransactionHistoryInterface $transactionHistory)
    {
        $this->transactionHistory = $transactionHistory;
        return $this;
    }
}
