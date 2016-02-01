<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 01.02.2016
 * Time: 12:15
 *
 */

namespace Blast\Db\Events;


class ValueEvent
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var string
     */
    private $key;

    /**
     * ResultEvent constructor.
     * @param string $name
     * @param boolean|array $value
     */
    public function __construct($name, $key, $value){
        $this->name = $name;
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * Return event name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array|bool
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param array|bool $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }
}