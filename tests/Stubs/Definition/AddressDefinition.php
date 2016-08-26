<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 21.07.2016
 * Time: 10:45
 *
 */

namespace Blast\Tests\Orm\Stubs\Definition;


use Blast\Orm\Entity\Definition;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Type;

class AddressDefinition extends Definition
{


    /**
     * ProjectDefinition constructor.
     */
    public function __construct()
    {
        $this->setConfiguration([
           'fields' => [
               new Column('userId', Type::getType(Type::INTEGER))
           ]
        ]);
    }
}
