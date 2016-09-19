<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 19.09.2016
 * Time: 15:03
 */

namespace Blast\Orm\Query;


use Blast\Orm\Connection;
use Blast\Orm\ConnectionManagerInterface;
use Blast\Orm\Locator\FactoryInterface;
use Blast\Orm\Locator\LocatorInterface;
use Blast\Orm\Query;

class QueryFactory implements FactoryInterface
{

    public function create($class, LocatorInterface $locator, array $arguments = [])
    {
        /** @var ConnectionManagerInterface $connectionManager */
        $connectionManager = $locator->get(ConnectionManagerInterface::class);

        /** @var Connection $connection */
        $connection = $connectionManager->get();

        return new Query($connection, $locator->get(ResultSetInterface::class));
    }
}
