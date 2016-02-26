<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 24.02.2016
 * Time: 09:55
 *
 */

namespace Blast\Orm\Entity;

use Blast\Orm\Query;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;
use League\Event\EmitterAwareInterface;

interface EntityAdapterInterface extends EmitterAwareInterface
{

    /**
     * Entity class name
     *
     * @return string
     */
    public function getClassName();

    /**
     * Table name
     *
     * @return string
     */
    public function getTableName();

    /**
     * Name of primary key
     *
     * @return string
     */
    public function getPrimaryKeyName();

    /**
     * @return Column[]
     */
    public function getFields();

    /**
     * @return Index[]
     */
    public function getIndexes();

    /**
     * @return Query[]
     */
    public function getRelations();

    /**
     * @param $data
     * @return mixed
     */
    public function hydrate($data);

    /**
     * @param Query $query
     *
     * @return $this
     */
    public function setQuery(Query $query);

    /**
     * Get modified query
     *
     * @return Query
     */
    public function getQuery();

}