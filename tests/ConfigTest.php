<?php

/**
 * Created by PhpStorm.
 * User: Marco Bunge
 * Date: 26.11.2015
 * Time: 13:40
 */

namespace Blast\Tests\Orm;

use Blast\Db\Config;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use PHPUnit_Framework_TestCase;

class ConfigTest extends PHPUnit_Framework_TestCase
{


    public function testAddConnectionString()
    {
        $config = new Config();
        $config->addConnection('string', 'sqlite:///:memory:');

        $this->assertInstanceOf(Connection::class, $config->getConnection('string'));
    }

    public function testAddConnectionArray()
    {
        $config = new Config();
        $config->addConnection('array', [
                'url' => 'sqlite:///:memory:',
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
            'url' => 'sqlite:///:memory:',
            'memory' => true
        ], $dbalConfiguration);

        $config->addConnection('object', $connection);

        $this->assertInstanceOf(Connection::class, $config->getConnection('object'));
    }

    public function testGetConnections(){
        $config = new Config();
        $config->addConnection('string', 'sqlite:///:memory:');
        $config->addConnection('string2', 'sqlite:///:memory:');

        $this->assertArrayHasKey('string', $config->getConnections());
        $this->assertArrayHasKey('string2', $config->getConnections());
    }
}
