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

use Blast\Orm\MapperAwareInterface;
use Blast\Orm\Query;
use Blast\Orm\Relations\RelationsAwareInterface;
use League\Event\EmitterAwareInterface;

interface EntityAdapterInterface extends EntityHydratorInterface, EmitterAwareInterface, FieldAwareInterface,
    IndexAwareInterface, MapperAwareInterface, PrimaryKeyAwareInterface,
    RelationsAwareInterface, TableNameAwareInterface
{

    const IS_PROPERTY = 256;
    const IS_METHOD = 512;
    const IS_CONSTANT = 1024;

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

}