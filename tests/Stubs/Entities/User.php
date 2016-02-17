<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 10.02.2016
 * Time: 08:16
 *
 */

namespace Stubs\Entities;


use Blast\Db\Data\AccessorTrait;
use Blast\Db\Data\ImmutableDataObjectTrait;
use Blast\Db\Data\MutableDataObjectTrait;
use Blast\Db\Orm\MapperAwareTrait;
use Blast\Db\Orm\Model\ModelInterface;
use Blast\Db\Orm\Model\ModelTrait;
use Blast\Db\Schema\Table;
use Doctrine\DBAL\Types\Type;

class User implements ModelInterface
{

    use AccessorTrait;
    use ImmutableDataObjectTrait;
    use MapperAwareTrait;
    use ModelTrait;
    use MutableDataObjectTrait;

    /**
     * Configure entity
     */
//    public function configure()
//    {
//        $table = new Table('user');
//        $table->addColumn('id', Type::INTEGER);
//        $table->addColumn('name', Type::STRING);
//        $table->setPrimaryKey(['id']);
//        $this->setTable($table);
//    }

    public static function getTable(){
        return 'user';
    }
}