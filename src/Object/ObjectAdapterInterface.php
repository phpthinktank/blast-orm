<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 24.02.2016
 * Time: 10:42
 *
 */

namespace Blast\Orm\Object;


interface ObjectAdapterInterface
{

    /**
     * @return mixed
     */
    public function getObject();

    /**
     * @param string|object $class
     * @return $this
     */
    public function setObject($class);

}