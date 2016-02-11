<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 11.02.2016
 * Time: 12:11
 *
 */

namespace Stubs;

class HookSubject
{

    public $immutable = null;

    /**
     * Execute hook
     * @param $config
     * @return array|mixed
     */
    public function initConfig($config)
    {
        $config['name'] = __CLASS__;
        return $config;
    }

    /**
     * Initialize services
     *
     * @param $config
     * @return mixed
     */
    public function initServices($config)
    {
        $config['services'] = [
            'Router',
            'Filter'
        ];

        return $config;
    }

    /**
     * Initialize services
     *
     * @param $config
     * @return mixed
     */
    public function initImmutable($config)
    {
        $config['immutable'] = $this->immutable;

        return $config;
    }


}