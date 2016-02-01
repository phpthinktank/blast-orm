<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 25.01.2016
 * Time: 16:08
 *
 */

namespace Blast\Db\Events;


use League\Event\AbstractEvent;

class ResultEvent extends AbstractEvent
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var boolean|array
     */
    private $result;

    /**
     * ResultEvent constructor.
     * @param string $name
     * @param boolean|array $result
     */
    public function __construct($name, $result){
        $this->name = $name;
        $this->result = $result;
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
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param array|bool $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }


}