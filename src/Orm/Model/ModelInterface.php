<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 11.02.2016
 * Time: 10:38
 *
 */

namespace Blast\Db\Orm\Model;


use Blast\Db\Data\AccessorInterface;
use Blast\Db\Data\DataObjectInterface;
use Blast\Db\Data\UpdatedDataObjectInterface;
use Blast\Db\Orm\MapperAwareInterface;

interface ModelInterface extends DataObjectInterface, UpdatedDataObjectInterface, AccessorInterface, MapperAwareInterface
{

    /**
     * Check if entry is new or already exists
     *
     * @return boolean
     */
    public function isNew();

}