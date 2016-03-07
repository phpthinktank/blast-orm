<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 12.02.2016
 * Time: 09:31
 *
 */

namespace Blast\Orm\Entity;


use Blast\Orm\Data\DataObject;
use Blast\Orm\Data\MutatorTrait;
use Blast\Orm\Data\AccessorTrait;

/**
 * Class Result
 * @package Blast\Db
 */
class Entity extends DataObject implements EntityInterface
{
    use AccessorTrait;
    use MutatorTrait;
}