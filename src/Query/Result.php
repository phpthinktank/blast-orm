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

namespace Blast\Db\Query;


use Blast\Db\Data\AccessorInterface;
use Blast\Db\Data\DataObject;
use Blast\Db\Data\MutatorInterface;
use Blast\Db\Data\MutatorTrait;
use Blast\Db\Data\AccessorTrait;

/**
 * Class Result
 * @package Blast\Db
 */
class Result extends DataObject implements AccessorInterface, MutatorInterface{

    use AccessorTrait;
    use MutatorTrait;

}