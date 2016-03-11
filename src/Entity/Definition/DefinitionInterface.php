<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 07.03.2016
 * Time: 12:03
 *
 */

namespace Blast\Orm\Entity\Definition;


use Blast\Orm\Entity\EntityAwareInterface;
use Blast\Orm\Entity\FieldAwareInterface;
use Blast\Orm\Entity\IndexAwareInterface;
use Blast\Orm\Entity\PrimaryKeyAwareInterface;
use Blast\Orm\Entity\TableNameAwareInterface;
use Blast\Orm\MapperAwareInterface;

interface DefinitionInterface extends FieldAwareInterface, IndexAwareInterface,
    MapperAwareInterface, PrimaryKeyAwareInterface, TableNameAwareInterface, EntityAwareInterface
{

}