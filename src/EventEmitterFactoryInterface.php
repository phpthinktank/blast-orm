<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 17.03.2016
 * Time: 12:09
 *
 */

namespace Blast\Orm;


interface EventEmitterFactoryInterface
{
    /**
     * Create event emitter and set optional events as array or \League\Event\ListenerProviderInterface.
     *
     * Event array could have a name-handler-pair, a listener provider as instance of
     * `\League\Event\ListenerProviderInterface` or a argument array with name, handler and priority
     *
     * ```
     * $events = [
     *      // name-handler-pair
     *      'eventName' => function(){},
     *
     *      // listener provider as instance of \League\Event\ListenerProviderInterface
     *      new \Acme\MyListenerProvider
     *
     *      // argument array name, handler, prio
     *      ['name', function(){}, 10]
     *
     *      // alternating argument array name => [handler, prio]
     *      'eventName' => [function(){}, 10]
     *
     * ];
     * ```
     *
     * @param array $events
     *
     * @return \League\Event\Emitter
     */
    public function createEventEmitter($events = []);
}
