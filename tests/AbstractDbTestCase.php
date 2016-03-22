<?php

/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 15.03.2016
 * Time: 10:58
 *
 */

namespace Blast\Tests\Orm;

use Blast\Orm\ConnectionManager;
use Blast\Orm\ConnectionManagerInterface;

abstract class AbstractDbTestCase extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $manager = ConnectionManager::getInstance();
        $connection = $manager->add([
            'url' => 'sqlite:///:memory:',
            'memory' => 'true'
        ])->get();

        $connection->exec('CREATE TABLE post (id int, user_pk int, title VARCHAR(255), content TEXT, date DATETIME DEFAULT CURRENT_DATE )');
        $connection->exec('CREATE TABLE users (pk int, name VARCHAR(255))');
        $connection->exec('CREATE TABLE addresses (id int, user_pk int, address TEXT)');
        $connection->exec('CREATE TABLE user_role (user_pk int, role_id int)');
        $connection->exec('CREATE TABLE roles (id int, name VARCHAR(255))');
        $connection->insert('post', [
            'id' => 1,
            'user_pk' => 1,
            'title' => 'Hello World',
            'content' => 'Some text',
        ]);
        $connection->insert('post', [
            'id' => 2,
            'user_pk' => 1,
            'title' => 'Next thing',
            'content' => 'More text to read'
        ]);
        $connection->insert('users', [
            'pk' => 1,
            'name' => 'Franz'
        ]);
        $connection->insert('user_role', [
            'user_pk' => 1,
            'role_id' => 1
        ]);
        $connection->insert('addresses', [
            'id' => 1,
            'user_pk' => 1,
            'address' => 'street 42, 11111 city'
        ]);
        $connection->insert('roles', [
            'id' => 1,
            'name' => 'Admin'
        ]);
    }

    protected function tearDown()
    {
        $manager = ConnectionManager::getInstance();
        $connection = $manager->get(ConnectionManagerInterface::DEFAULT_CONNECTION);

        $connection->exec('DROP TABLE post');
        $connection->exec('DROP TABLE users');
        $connection->exec('DROP TABLE addresses');
        $connection->exec('DROP TABLE user_role');
        $connection->exec('DROP TABLE roles');

        $manager->closeAll();
    }
}
