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

namespace Blast\Orm\Data;


trait IteratorTrait
{
    /**
     * reset pointer on data
     */
    public function rewind() {
        reset($this->data);
    }

    /**
     * Get current entity
     * @return mixed
     */
    public function current() {
        return current($this->data);
    }

    /**
     * Get current position
     * @return string|int
     */
    public function key() {
        return key($this->data);
    }

    /**
     * Set pointer to next entity and return entity
     * @return mixed
     */
    public function next() {
        return next($this->data);
    }

    /**
     * check if current value is valid
     * @return bool
     */
    public function valid() {
        return $this->current() !== false;
    }
}