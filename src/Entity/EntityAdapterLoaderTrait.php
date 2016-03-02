<?php
/*
*
* (c) Marco Bunge <marco_bunge@web.de>
*
* For the full copyright and license information, please view the LICENSE.txt
* file that was distributed with this source code.
*
* Date: 24.02.2016
* Time: 23:43
*/

namespace Blast\Orm\Entity;

trait EntityAdapterLoaderTrait
{
    /**
     * @param object $entity
     * @return EntityAdapter
     */
    public function loadAdapter($entity)
    {
        // @todo instead of creating a new instance use a cached entity instance
        return EntityAdapter::load($entity);
    }
}