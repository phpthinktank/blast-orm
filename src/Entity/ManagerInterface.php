<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 25.01.2016
 * Time: 13:51
 *
 */

namespace Blast\Db\Entity;


interface ManagerInterface
{

    /**
     * Build entity with fields and events, set fields from attached entity
     * @param EntityInterface|array $previous
     * @return EntityInterface
     */
    public function create($previous = null);

}