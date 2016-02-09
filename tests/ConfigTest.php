<?php

/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 26.11.2015
 * Time: 13:40
 */

namespace Blast\Tests\Db;

use Blast\Db\Config;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use PHPUnit_Framework_TestCase;

class ConfigTest extends PHPUnit_Framework_TestCase
{
    protected $dsn = 'sqlite:///:memory:';

    public function testAddConnectionString()
    {
        $config = new Config();
        $config->addConnection('string', $this->dsn);

        $this->assertInstanceOf(Connection::class, $config->getConnection('string'));
    }

    public function testAddConnectionArray()
    {
        $config = new Config();
        $config->addConnection('array', [
                'url' => $this->dsn,
                'memory' => true
            ]
        );

        $this->assertInstanceOf(Connection::class, $config->getConnection('array'));
    }

    public function testAddConnectionObject()
    {
        $config = new Config();

        $dbalConfiguration = new Configuration();
        $connection = DriverManager::getConnection([
            'url' => $this->dsn,
            'memory' => true
        ], $dbalConfiguration);

        $config->addConnection('object', $connection);

        $this->assertInstanceOf(Connection::class, $config->getConnection('object'));
    }

    public function testGetConnections()
    {
        $config = new Config();
        $config->addConnection('string', $this->dsn);
        $config->addConnection('string2', $this->dsn);

        $this->assertArrayHasKey('string', $config->getConnections());
        $this->assertArrayHasKey('string2', $config->getConnections());
    }
}
