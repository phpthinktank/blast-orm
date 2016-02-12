<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 12.02.2016
 * Time: 10:42
 *
 */

namespace Blast\Db\Data;

class ImmutableDataObject implements \Countable, FilterableInterface, ImmutableDataObjectInterface,\Iterator
{
    use ConverterTrait;
    use CountableTrait;
    use FilterableTrait;
    use ImmutableDataObjectTrait;
    use IteratorTrait;

    /**
     * @var array
     */
    private $data = [];

    /**
     * Replace data
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
        return $this;
    }
}