<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 25.11.2015
 * Time: 16:39
 */

namespace Blast\Db\Orm;


use Interop\Container\ContainerInterface;

interface FactoryInterface
{

    /**
     * Create a new orm capsule
     * @param ContainerInterface $container
     * @param array $connection
     * @return $this
     */
    public static function create(ContainerInterface $container, $connection);

    /**
     * @return $this
     */
    public static function getInstance();

    /**
     * @return ContainerInterface
     */
    public function getContainer();

    /**
     * @return ConfigInterface
     */
    public function getConfig();


}