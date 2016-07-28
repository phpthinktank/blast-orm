<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 23.02.2016
 * Time: 10:20
 *
 */
namespace Blast\Orm\Entity;

trait EntityAwareTrait
{
    /**
     * @var array|\stdClass|\ArrayObject|object
     */
    private $entity;

    /**
     * @return array|\stdClass|\ArrayObject|object
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param array|\ArrayObject|\stdClass|object|string $entity
     * @return $this
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
        return $this;
    }
}
