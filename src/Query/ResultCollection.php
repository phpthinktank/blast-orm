<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 12.02.2016
 * Time: 11:37
 *
 */

namespace Blast\Db\Query;


use Blast\Db\Data\DataObject;

class ResultCollection extends DataObject
{
    /**
     * @var Query
     */
    private $query;

    public function __construct(Query $query = null)
    {
        $this->query = $query;
    }

    /**
     * @return Query
     */
    public function getQuery()
    {
        return $this->query;
    }

}