<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 25.11.2015
 * Time: 16:19
 */

namespace Blast\Orm;

/**
 * Provide access and caching of connections.
 *
 * @package Blast\Orm
 */
interface ConnectionManagerInterface
{
    const DEFAULT_CONNECTION = 'default';

    /**
     *
     * Params a related to configuration
     *
     * @see http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#getting-a-connection
     *
     * @param array|\Doctrine\DBAL\Connection|string $connection
     * @param $name
     * @return $this
     */
    public function add($connection, $name);

    /**
     * Activate a connection as default connection
     * @param string $name
     */
    public function swapActiveConnection($name);

    /**
     * @param $name
     * @return \Doctrine\DBAL\Connection
     */
    public function get($name = null);

    /**
     * @return \Doctrine\DBAL\Connection[]
     */
    public function all();

}
