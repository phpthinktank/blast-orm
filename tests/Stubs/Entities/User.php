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
     * @var \ArrayObject|Post
     */
    private $post;

    /**
     * @var \ArrayObject
     */
    private $address;

    public static function primaryKeyName(){
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
     * @return \ArrayObject|Post
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @return \ArrayObject
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param \ArrayObject $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

}