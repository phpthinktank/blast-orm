<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 25.11.2015
 * Time: 17:40
 */

namespace Blast\Db\Entity;

use Blast\Db\Orm\MapperAwareInterface;
use Blast\Db\Relations\RelationAwareInterface;
use Blast\Db\Schema\Table;
use League\Event\EmitterInterface;

interface EntityInterface extends AccessorInterface, MutatorInterface, DataObjectInterface, UpdatedDataObjectInterface, FlushDataObjectInterface, MapperAwareInterface, RelationAwareInterface
{

    /**
     *
     */
    public function __construct();

    /**
     * @return Table
     */
    public function getTable();

    /**
     * @return EmitterInterface
     */
    public function getEmitter();

    /**
     *
     */
    public function configure();


}