<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 23.02.2016
 * Time: 10:19
 *
 */

namespace Blast\Orm\Query;


use Blast\Orm\Data\DataObject;

interface QueryInterface
{
    /**
     * Fetch data for entity
     *
     * @param string $option
     * @return array|Result|DataObject
     * @throws \Doctrine\DBAL\DBALException
     */
    public function execute($option = ResultDataDecorator::AUTO);
}