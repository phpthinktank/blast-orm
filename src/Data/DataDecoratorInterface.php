<?php
/*
*
* (c) Marco Bunge <marco_bunge@web.de>
*
* For the full copyright and license information, please view the LICENSE.txt
* file that was distributed with this source code.
*
* Date: 19.02.2016
* Time: 13:29
*/

namespace Blast\Db\Data;


interface DataDecoratorInterface extends DataObjectInterface
{
    /**
     *
     */
    const AUTO = 'auto';

    /**
     * Decorate data
     *
     * @param string $option
     * @return mixed
     */
    public function decorate($option = self::AUTO);
}