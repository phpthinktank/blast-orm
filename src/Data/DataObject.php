<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 12.02.2016
 * Time: 10:25
 *
 */

namespace Blast\Db\Data;

class DataObject implements \Countable, DataObjectInterface, FilterableInterface, \Iterator
{
    use ConverterTrait;
    use CountableTrait;
    use MutableDataObjectTrait;
    use FilterableTrait;
    use ImmutableDataObjectTrait;
    use IteratorTrait;

}