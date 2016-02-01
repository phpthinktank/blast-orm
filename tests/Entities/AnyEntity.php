<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 26.11.2015
 * Time: 17:04
 */

namespace Blast\Tests\Orm\Entities;


use Blast\Db\Orm\Entity\AbstractEntity;
use Blast\Db\Schema\Table;
use Doctrine\DBAL\Types\Type;

class AnyEntity extends AbstractEntity
{
    protected $table = 'test';
    protected $primaryKeyField = 'id';


    /**
     * Configure entity
     */
    public function configure()
    {
        $table = new Table('test');
        $table->addColumn('id', Type::INTEGER);
        $table->setPrimaryKey(['id']);
        $this->setTable($table);

        $this->getEmitter()->addListener(self::BEFORE_GET, function(){
           echo 'get all values!';
        });
    }
}