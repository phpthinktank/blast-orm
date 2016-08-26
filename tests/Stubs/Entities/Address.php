<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 02.03.2016
 * Time: 11:06
 *
 */

namespace Blast\Tests\Orm\Stubs\Entities;
use Blast\Tests\Orm\Stubs\Definition\AddressDefinition;

/**
 * @codeCoverageIgnore
 */
class Address
{

    public static $definition = AddressDefinition::class;

    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $user_id;

    /**
     * @var int
     */
    private $fullName;

    /**
     * @var string
     */
    private $address;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return int
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * @param int $fullName
     * @return Address
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;
        return $this;
    }

}
