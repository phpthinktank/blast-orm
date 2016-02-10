<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 26.11.2015
 * Time: 17:04
 */

namespace Blast\Tests\Db\Stubs\Entities;

use Blast\Db\Entity\AbstractEntity;
use Blast\Db\Relations\BelongsTo;
use Blast\Db\Schema\Table;
use Doctrine\DBAL\Types\Type;
use Stubs\Entities\User;

class Post extends AbstractEntity
{

    /**
     * Configure entity
     */
    public function configure()
    {
        $table = new Table('post');
        $table->addColumn('id', Type::INTEGER);
        $table->addColumn('user_id', Type::INTEGER);
        $table->addColumn('same', Type::INTEGER);
        $table->setPrimaryKey(['id']);
        $this->setTable($table);

        $this->addRelation(new BelongsTo($this, new User()));
    }
}