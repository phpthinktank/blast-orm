<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 25.11.2015
 * Time: 17:48
 */

namespace Blast\Db\Entity;

use Blast\Db\Data\ConverterTrait;
use Blast\Db\Orm\Mapper;
use Blast\Db\Orm\MapperAwareTrait;
use Blast\Db\Relations\RelationManagerTrait;
/**
 *
 * @deprecated
 */
abstract class AbstractEntity implements EntityInterface
{

    use EntityTrait;
    use MapperAwareTrait {
        getMapper as getInternalMapper;
    }
    use RelationManagerTrait;
    use ConverterTrait;

    /**
     * Get mapper and lazy instantiate mapper if no mapper exists
     * @return \Blast\Db\Orm\MapperInterface
     */
    public function getMapper()
    {
        if($this->mapper === null){
            $this->mapper = new Mapper($this);
        }
        return $this->getInternalMapper();
    }

}