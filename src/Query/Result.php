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

namespace Blast\Orm\Query;


use Blast\Orm\Data\AccessorInterface;
use Blast\Orm\Data\DataObject;
use Blast\Orm\Data\MutatorInterface;
use Blast\Orm\Data\MutatorTrait;
use Blast\Orm\Data\AccessorTrait;

/**
 * Class Result
 * @package Blast\Db
 */
class Result extends DataObject implements ResultInterface
{

    use AccessorTrait;
    use MutatorTrait;

}