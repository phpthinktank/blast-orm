<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 11.02.2016
 * Time: 10:32
 *
 */

namespace Blast\Orm\Data;


interface AccessorInterface
{
    /**
     * Get value by name, if no value exists use default value instead
     *
     * @param $name
     * @param null $default
     * @return $this
     */
    public function get($name, $default = null);

    /**
     * Check if value exists
     *
     * @param $name
     * @return $this
     */
    public function has($name);

}