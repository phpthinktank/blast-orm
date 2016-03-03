<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 02.03.2016
 * Time: 15:50
 *
 */

namespace Blast\Orm\Container;

use Exception;
use Interop\Container\Exception\NotFoundException;

class DefinitionNotFoundException extends \Exception implements NotFoundException
{
    public function __construct($id = null, $code = 0, Exception $previous = null)
    {
        parent::__construct(sprintf('Service %s not found!', $id), $code, $previous);
    }

}