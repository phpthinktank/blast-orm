<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 11.02.2016
 * Time: 10:35
 *
 */

namespace Blast\Db\Entity;


interface UpdatedDataObjectInterface
{
    /**
     * @return boolean
     */
    public function isUpdated();

    /**
     * @return array
     */
    public function getUpdatedData();
}