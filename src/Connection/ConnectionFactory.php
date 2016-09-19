<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 19.09.2016
 * Time: 15:39
 */

namespace Blast\Orm\Connection;


use Blast\Orm\Connection;
use Blast\Orm\Locator\FactoryInterface;
use Blast\Orm\Locator\LocatorInterface;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Connection as DBALConnection;
use Doctrine\DBAL\DriverManager;

class ConnectionFactory implements FactoryInterface
{

    /**
     * Create a new connection from definition.
     *
     * If definition is a string, the manager tries to get definition from ioc container,
     * otherwise the manager assumes a valid dsn string and converts definition to an array.
     *
     * If definition is a string manager is determining wrapper class and tries to get wrapper
     * class from container.
     *
     * @param string $class
     * @param LocatorInterface $locator
     * @param array $arguments
     * @return Connection|DBALConnection
     * @throws DBALException
     */
    public function create($class, LocatorInterface $locator, array $arguments = [])
    {
        if(!isset($arguments['definition'])){
            throw new \InvalidArgumentException('Unable to load definition');
        }

        $definition = $arguments['definition'];

        // create connection from definition
        if ($definition instanceof DBALConnection) {
            return $definition;
        }

        // assume a valid service from IoC container
        // or assume a valid dsn and convert to connection array
        if (is_string($definition)) {
            $definition = ['url' => $definition];
        }

        if (!is_array($definition)) {
            throw new DBALException('Unable to determine parameter array from definition');
        }

        if (!array_key_exists('wrapperClass', $definition)) {
            $definition['wrapperClass'] = Connection::class;
        }

        $connection = DriverManager::getConnection($definition);

        if (!($connection instanceof Connection)) {
            throw new \RuntimeException(sprintf('Connection needs to be an instance of %s', Connection::class));
        }

        //setup special configuration for blast connections
        if ($connection instanceof Connection) {
            if (array_key_exists('prefix', $definition)) {
                $connection->setPrefix($definition['prefix']);
            }
        }

        return $connection;
    }
}
