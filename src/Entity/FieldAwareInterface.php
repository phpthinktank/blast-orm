<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 01.03.2016
 * Time: 09:34
 *
 */

namespace Blast\Orm\Entity;


use Doctrine\DBAL\Schema\Column;

interface FieldAwareInterface
{
    /**
     * @return Column[]
     */
    public function getFields();
}