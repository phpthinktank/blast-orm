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


use Blast\Orm\Data\AccessorTrait;
use Blast\Orm\Data\DataObjectInterface;
use Blast\Orm\Data\ImmutableDataObjectTrait;
use Blast\Orm\Data\MutableDataObjectTrait;

class User
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    public static function getTable(){
        return 'user';
    }

    public static function getPrimaryKeyName(){
        return 'pk';
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return User
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }


}