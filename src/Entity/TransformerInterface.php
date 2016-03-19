<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 17.03.2016
 * Time: 08:02
 *
 */

namespace Blast\Orm\Entity;

/**
 * Transform configuration into entity and entity definition.
 *
 * @package Blast\Orm\Entity
 */
interface TransformerInterface
{

    /**
     * Transform configuration into entity and entity definition. Configuration could be a
     * string (class name or table name), array (convert to a definition),
     * a Definition instance or an Entity instance.
     *
     * @param $configuration
     * @return mixed
     */
    public function transform($configuration);

}
