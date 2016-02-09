<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 09.02.2016
 * Time: 11:07
 *
 */

namespace Blast\Db\Orm;


trait MapperAwareTrait
{
    /**
     * @var MapperInterface
     */
    private $mapper;

    /**
     * @return MapperInterface
     */
    public function getMapper(){
        return $this->mapper;
    }

    /**
     * @param MapperInterface $mapper
     * @return $this
     */
    public function setMapper(MapperInterface $mapper){
        $this->mapper = $mapper;
        return $this;
    }
}