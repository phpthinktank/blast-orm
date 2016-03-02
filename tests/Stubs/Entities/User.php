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

namespace Blast\Tests\Orm\Stubs\Entities;


use Blast\Orm\Data\DataObject;
use Blast\Orm\Entity\GenericEntity;
use Blast\Orm\Relations\HasMany;
use Blast\Orm\Relations\HasOne;
use Blast\Orm\Relations\ManyToMany;

/**
 * @codeCoverageIgnore
 */
class User
{
    /**
     * @var int
     */
    private $pk;

    /**
     * @var string
     */
    private $name;

    /**
     * @var DataObject|Post
     */
    private $post;

    /**
     * @var DataObject
     */
    private $address;

    public static function getPrimaryKeyName(){
        return 'pk';
    }

    /**
     * @return int
     */
    public function getPk()
    {
        return $this->pk;
    }

    /**
     * @param int $pk
     * @return User
     */
    public function setPk($pk)
    {
        $this->pk = $pk;
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

    /**
     * @return DataObject|Post
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @return DataObject
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param DataObject $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * Get relations
     * @param $entity
     * @return array
     */
    public static function relations($entity)
    {
        return [
            new HasMany($entity, Post::class, 'user_id'),
            new HasOne($entity, new GenericEntity('address'), 'user_id'),
//            new ManyToMany($entity, new GenericEntity('role')),
        ];
    }

}