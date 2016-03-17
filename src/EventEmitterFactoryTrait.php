<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 17.03.2016
 * Time: 11:27
 *
 */

namespace Blast\Orm;


use League\Event\Emitter;
use League\Event\EmitterInterface;
use League\Event\ListenerAcceptorInterface;
use League\Event\ListenerInterface;
use League\Event\ListenerProviderInterface;

trait EventEmitterFactoryTrait
{

    /**
     * Create event emitter and set optional events as array or \League\Event\ListenerProviderInterface.
     * Set as second argument a emitter instance, otherwise the factory creates a new one.
     *
     * Event array could have a name-handler-pair, a listener provider as instance of
     * `\League\Event\ListenerProviderInterface` or a argument array with name, handler and priority
     *
     * Configure event array as follows:
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
     * @param \League\Event\ListenerProviderInterface|array $events
     * @param \League\Event\EmitterInterface $emitter
     *
     * @return Emitter
     */
    public function createEventEmitter($events = [], EmitterInterface $emitter = null)
    {
        if(null === $emitter){
            $emitter = new Emitter();
        }

        if (!is_array($events)) {
            $events = [$events];
        }

        if (!empty($events)) {
            foreach ($events as $name => $handler) {

                if ($handler instanceof ListenerProviderInterface) {
                    $emitter->useListenerProvider($handler);
                    continue;
                }

                $args = is_array($handler) ?
                    array_merge([$name], $handler) :
                    [$name, $handler, EmitterInterface::P_NORMAL];

                call_user_func_array([$emitter, 'addListener'], $args);
            }
        }

        return $emitter;
    }

}
