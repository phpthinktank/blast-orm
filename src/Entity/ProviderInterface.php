<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 07.03.2016
 * Time: 12:03
 *
 */

namespace Blast\Orm\Entity;


use Blast\Orm\MapperAwareInterface;
use Blast\Orm\Relations\RelationsAwareInterface;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;

interface ProviderInterface extends EntityAwareInterface
{

    const DEFAULT_PRIMARY_KEY_NAME = 'id';

    /**
     * @return DefinitionInterface
     */
    public function getDefinition();

}
