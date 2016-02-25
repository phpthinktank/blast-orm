<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 12.02.2016
 * Time: 13:21
 *
 */

namespace Blast\Tests\Orm\Stubs\Data;


use Blast\Orm\Data\AccessorTrait;

/**
 * @codeCoverageIgnore
 */
class ArrayObject extends \ArrayObject
{

    use AccessorTrait;

}