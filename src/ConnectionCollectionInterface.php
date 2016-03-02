<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 25.11.2015
 * Time: 16:19
 */

namespace Blast\Orm;


interface ConnectionCollectionInterface
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
    public function addConnection($connection, $name);

    /**
     * Activate a connection as default connection
     * @param string $name
     */
    public function setDefaultConnection($name);

    /**
     * @param $name
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection($name = null);

    /**
     * @return \Doctrine\DBAL\Connection[]
     */
    public function getConnections();

}