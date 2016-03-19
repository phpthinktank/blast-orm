<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 16.03.2016
 * Time: 17:22
 *
 */

namespace Blast\Orm\Entity;


trait ProviderFactoryTrait
{

    /**
     * Create a new provider for given entity
     *
     * @param $entity
     * @return Provider
     */
    public function createProvider($entity){
        return new Provider($entity);
    }

}
