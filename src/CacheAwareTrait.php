<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 27.07.2016
 * Time: 10:22
 *
 */

namespace Blast\Orm;


trait CacheAwareTrait
{

    /**
     * @return \Doctrine\Common\Cache\Cache
     */
    public function getMetaDataCache(){
        return ConnectionManager::getInstance()->get()->getMetaDataCache();
    }

    /**
     * @return \Doctrine\Common\Cache\Cache
     */
    public function getReflectionCache(){
        return ConnectionManager::getInstance()->get()->getReflectionCache();
    }

}
