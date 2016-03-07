<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 23.02.2016
 * Time: 10:16
 *
 */

namespace Blast\Orm\Entity;


use Blast\Orm\Data\AccessorInterface;
use Blast\Orm\Data\DataObjectInterface;
use Blast\Orm\Data\FilterableInterface;
use Blast\Orm\Data\MutatorInterface;

interface EntityInterface extends AccessorInterface, \Countable, DataObjectInterface, FilterableInterface, \Iterator, MutatorInterface
{

}