<?php
/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 25.11.2015
 * Time: 16:19
 */

namespace Blast\Db;


interface ConfigInterface
{

    /**
     *
     * Params a related to configuration
     *
     * @see http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#getting-a-connection
     *
     * @param $name
     * @param array|\Doctrine\DBAL\Connection|string $connection
     * @return $this
     */
    public function addConnection($name, $connection);

    /**
     * @param $name
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection($name);

    /**
     * @return \Doctrine\DBAL\Connection[]
     */
    public function getConnections();

}