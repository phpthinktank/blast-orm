<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 10.02.2016
 * Time: 08:16
 *
 */

namespace Stubs\Entities;


use Blast\Orm\Data\AccessorTrait;
use Blast\Orm\Data\DataObjectInterface;
use Blast\Orm\Data\ImmutableDataObjectTrait;
use Blast\Orm\Data\MutableDataObjectTrait;

class User implements DataObjectInterface
{

    use AccessorTrait;
    use ImmutableDataObjectTrait;
    use MutableDataObjectTrait;

    public static function getTable(){
        return 'user';
    }
}