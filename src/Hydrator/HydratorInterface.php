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

namespace Blast\Orm\Hydrator;


use Zend\Hydrator\ExtractionInterface;

interface HydratorInterface extends ExtractionInterface
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

    /**
     *
     */
    const HYDRATE_AUTO = 'auto';

    /**
     * @param array $data
     * @param string $option
     * @return mixed
     */
    public function hydrate($data = [], $option = self::HYDRATE_AUTO);
}
