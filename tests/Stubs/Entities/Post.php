<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 26.11.2015
 * Time: 17:04
 */

namespace Blast\Tests\Db\Stubs\Entities;

use Blast\Db\Data\ImmutableDataObjectTrait;
use Blast\Db\Data\MutableDataObjectTrait;
use Blast\Db\Orm\Model\ModelInterface;
use Blast\Db\Orm\Model\ModelTrait;

class Post implements ModelInterface
{
    use MutableDataObjectTrait;
    use ImmutableDataObjectTrait;
    use ModelTrait;

    /**
     * Configure entity
     */
//    public function configure()
//    {
//        $table = new Table('post');
//        $table->addColumn('id', Type::INTEGER);
//        $table->addColumn('user_id', Type::INTEGER);
//        $table->addColumn('title', Type::STRING)->setLength(255);
//        $table->addColumn('content', Type::TEXT);
//        $table->setPrimaryKey(['id']);
//        $this->setTable($table);
//
//        $this->addRelation(new BelongsTo($this, new User()));
//
//        $this->getEmitter()->addListener(ModelEmitterAwareInterface::VALUE_GET, function(DecoratorEvent $event){
//            if($event->getKey() === 'title'){
//                $event->setValue(sprintf('<h1>%s</h1>', $event->getValue()));
//            }
//        });
//
//        $this->getEmitter()->addListener(ModelEmitterAwareInterface::VALUE_GET, function(DecoratorEvent $event){
//            if($event->getKey() === 'content'){
//                $event->setValue(strip_tags($event->getValue()));
//            }
//        });
//    }

    /**
     * Get table for model
     *
     * @return string
     */
    public static function getTable()
    {
        return 'post';
    }
}