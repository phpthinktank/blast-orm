<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 03.03.2016
 * Time: 10:06
 *
 */

namespace Blast\Orm\Entity;


use Blast\Orm\Facades\AbstractFacade;
use Blast\Orm\Facades\FacadeFactory;
use Blast\Orm\Query\Result;

/**
 * Class EntityAdapterCollectionFacade
 * @method static Result|mixed|object createObject($object)
 * @method static EntityAdapter get($object, $adapterClassName = null)
 */
class EntityAdapterCollectionFacade extends AbstractFacade
{
    protected static function accessor()
    {
        $accessor = EntityAdapterCollection::class;

        if(!FacadeFactory::getContainer()->has($accessor)){
            FacadeFactory::getContainer()->add($accessor, new EntityAdapterCollection(), true);
        }

        return $accessor;
    }
}