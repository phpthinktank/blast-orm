<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 12.02.2016
 * Time: 10:35
 *
 */

namespace Blast\Db\Data;


trait IteratorTrait
{
    /**
     * reset pointer on data
     */
    public function rewind() {
        $data = DataHelper::receiveDataFromObject($this);
        reset($data);
    }

    /**
     * Get current entity
     * @return mixed
     */
    public function current() {
        $data = DataHelper::receiveDataFromObject($this);
        return current($data);
    }

    /**
     * Get current position
     * @return string|int
     */
    public function key() {
        $data = DataHelper::receiveDataFromObject($this);
        return key($data);
    }

    /**
     * Set pointer to next entity and return entity
     * @return mixed
     */
    public function next() {
        $data = DataHelper::receiveDataFromObject($this);
        return next($data);
    }

    /**
     * check if current value is valid
     * @return bool
     */
    public function valid() {
        return $this->current() !== false;
    }
}