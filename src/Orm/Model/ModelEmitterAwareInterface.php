<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 11.02.2016
 * Time: 10:40
 *
 */

namespace Blast\Db\Orm\Model;


use League\Event\EmitterAwareInterface;

interface ModelEmitterAwareInterface extends EmitterAwareInterface
{

    const BEFORE_SAVE = 'save.before';
    const AFTER_SAVE = 'save.before';
    const BEFORE_CREATE = 'create.before';
    const AFTER_CREATE = 'create.before';
    const BEFORE_UPDATE = 'update.before';
    const AFTER_UPDATE = 'update.before';
    const BEFORE_DELETE = 'delete.before';
    const AFTER_DELETE = 'delete.before';
    const VALUE_GET = 'value.get';
    const VALUE_SET = 'value.set';

}