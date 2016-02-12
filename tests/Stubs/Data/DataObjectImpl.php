<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 12.02.2016
 * Time: 13:23
 *
 */

namespace Blast\Tests\Db\Stubs\Data;


use Blast\Db\Data\DataObject;
use Blast\Db\Data\MutatorTrait;
use Blast\Db\Data\AccessorTrait;

class DataObjectImpl extends DataObject
{
    use AccessorTrait;
    use MutatorTrait;

}