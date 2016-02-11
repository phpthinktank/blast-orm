<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 11.02.2016
 * Time: 10:31
 *
 */

namespace Blast\Db\Entity;


interface DataObjectInterface
{
    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data);

    /**
     * @return array
     */
    public function getData();

}