<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 15.03.2016
 * Time: 10:43
 *
 */

namespace Blast\Orm;


trait LocatorAwareTrait
{

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * Get a locator instance
     *
     * @return LocatorInterface
     */
    public function getLocator(){
        return $this->locator;
    }
}
