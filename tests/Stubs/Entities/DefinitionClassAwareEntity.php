<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 21.07.2016
 * Time: 10:43
 *
 */

namespace Blast\Tests\Orm\Stubs\Entities;


use Blast\Tests\Orm\Stubs\Definition\ProjectDefinition;

class DefinitionClassAwareEntity
{

    public static $definition = ProjectDefinition::class;

}
