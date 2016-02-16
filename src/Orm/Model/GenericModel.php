<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 16.02.2016
 * Time: 09:47
 *
 */

namespace Blast\Db\Orm\Model;


use Blast\Db\Data\AccessorTrait;
use Blast\Db\Data\DataObject;
use Blast\Db\Data\MutatorTrait;
use Blast\Db\Orm\MapperAwareTrait;

class GenericModel extends DataObject implements ModelInterface
{

    use AccessorTrait;
    use MapperAwareTrait;
    use MutatorTrait;
    use ModelTrait;


    /**
     * @var array
     */
    private $originalData = [];

    public function __construct($table, $fields = [])
    {

    }
}