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

namespace Blast\Orm;


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
     * @return Query
     */
    public function setEntity($entity)
    {
        if (is_string($entity)) {
            $container = Manager::getInstance()->getContainer();
            $entity = $container->has($entity) ? $container->get($entity) : (new \ReflectionClass($entity))->newInstance();
        }
        $this->entity = $entity;
        return $this;
    }
}