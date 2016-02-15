<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 11.02.2016
 * Time: 11:25
 *
 */

namespace Blast\Db\Orm\Model;


use Blast\Db\Manager;
use League\Event\Emitter;
use League\Event\EmitterAwareTrait;
use League\Event\EmitterInterface;

class Model
{

    use EmitterAwareTrait;

    /**
     * @return Emitter
     */
    public function getEmitter()
    {
        if ($this->emitter === null) {
            $container = Manager::getInstance()->getContainer();
            $this->emitter = $container->get(EmitterInterface::class) ? $container->get(EmitterInterface::class) : new Emitter();
        }
        return $this->emitter;
    }

}