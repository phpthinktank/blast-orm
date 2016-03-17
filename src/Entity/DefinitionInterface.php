<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 17.03.2016
 * Time: 08:08
 *
 */

namespace Blast\Orm\Entity;

use Blast\Orm\MapperAwareInterface;
use Blast\Orm\Relations\RelationsAwareInterface;
use League\Event\EmitterAwareInterface;

/**
 * All entity definition relating to schema definition
 *
 * @package Blast\Orm\Entity
 */
interface DefinitionInterface extends EmitterAwareInterface, MapperAwareInterface, RelationsAwareInterface
{
    /**
     * @return \ArrayObject|object
     */
    public function getEntity();

    /**
     * @return \SplStack|object
     */
    public function getEntityCollection();

    /**
     * @return \Doctrine\DBAL\Schema\Column[]
     */
    public function getFields();

    /**
     * @return \Doctrine\DBAL\Schema\Index[]
     */
    public function getIndexes();

    /**
     * Name of primary key
     *
     * @return string
     */
    public function getPrimaryKeyName();

    /**
     * Table name
     *
     * @return string
     */
    public function getTableName();
}
