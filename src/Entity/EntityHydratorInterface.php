<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 01.03.2016
 * Time: 15:46
 *
 */

namespace Blast\Orm\Entity;


use Blast\Orm\Data\DataHydratorInterface;

interface EntityHydratorInterface extends DataHydratorInterface
{
    /**
     *
     */
    const HYDRATE_COLLECTION = 'collection';
    /**
     *
     */
    const HYDRATE_ENTITY = 'entity';

    /**
     *
     */
    const HYDRATE_RAW = 'raw';
}