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

use Blast\Orm\Data\DataHydratorInterface;
use Blast\Orm\MapperInterface;
use Blast\Orm\Query;
use Blast\Orm\Relations\RelationsAwareInterface;
use League\Event\EmitterAwareInterface;

interface EntityAdapterInterface extends EmitterAwareInterface, DataHydratorInterface, FieldAwareInterface,
    IndexAwareInterface, PrimaryKeyAwareInterface, TableNameAwareInterface, RelationsAwareInterface
{
    const DEFAULT_PRIMARY_KEY_NAME = 'id';

    /**
     *
     */
    const HYDRATE_COLLECTION = 'collection';
    /**
     *
     */
    const HYDRATE_ENTITY = 'entity';

    /**
     *
     */
    const HYDRATE_RAW = 'raw';

    /**
     * Entity class name
     *
     * @return string
     */
    public function getClassName();

    /**
     * Fetch all data without relations
     */
    public function getDataWithoutRelations();

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

    /**
     * Get entity mapper
     *
     * @return MapperInterface
     */
    public function getMapper();

}