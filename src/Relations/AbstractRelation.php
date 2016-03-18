<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 18.03.2016
 * Time: 10:18
 *
 */

namespace Blast\Orm\Relations;


use Blast\Orm\ConnectionAwareInterface;
use Blast\Orm\ConnectionAwareTrait;
use Blast\Orm\Entity\ProviderFactoryInterface;
use Blast\Orm\Entity\ProviderFactoryTrait;
use Blast\Orm\TransactionHistoryAwareInterface;
use Blast\Orm\TransactionHistoryAwareTrait;

abstract class AbstractRelation implements ConnectionAwareInterface, RelationInterface, ProviderFactoryInterface,
    TransactionHistoryAwareInterface
{
    use ConnectionAwareTrait;
    use ProviderFactoryTrait;
    use RelationTrait;
    use TransactionHistoryAwareTrait;

}
