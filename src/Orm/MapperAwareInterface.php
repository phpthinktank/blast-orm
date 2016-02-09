<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 09.02.2016
 * Time: 11:06
 *
 */

namespace Blast\Db\Orm;


interface MapperAwareInterface
{

    /**
     * @return MapperInterface
     */
    public function getMapper();

    /**
     * @param MapperInterface $mapper
     * @return $this
     */
    public function setMapper(MapperInterface $mapper);

}