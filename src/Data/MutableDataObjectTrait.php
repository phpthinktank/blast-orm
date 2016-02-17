<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 17.02.2016
 * Time: 11:00
 *
 */

namespace Blast\Db\Data;


use Blast\Db\Hook;

trait MutableDataObjectTrait
{
    /**
     * Replace data
     * @param array $data
     * @return $this
     */
    public function setData(array $data = [])
    {
        $before = Hook::trigger('beforeDataSet', $this, ['data' => $data]);
        $data = isset($before['data']) ? $before['data'] : $data;
        $this->data = $data;
        return $this;
    }
}