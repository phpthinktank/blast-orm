<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 29.02.2016
 * Time: 11:19
 *
 */

namespace Blast\Tests\Orm;


use Blast\Orm\ConnectionCollectionInterface;
use Blast\Orm\ConnectionFacade;
use Blast\Orm\Data\DataObject;
use Blast\Orm\Entity\EntityAwareInterface;
use Blast\Orm\RepositoryInterface;
use Blast\Tests\Orm\Stubs\Entities\Post;
use Blast\Tests\Orm\Stubs\PostRepository;

class RepositoryTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        $connection = ConnectionFacade::addConnection( [
            'url' => 'sqlite:///:memory:',
            'memory' => 'true'
        ])->getConnection();

        $connection->exec('CREATE TABLE post (id int, user_id int, title VARCHAR(255), content TEXT)');
        $connection->exec('CREATE TABLE user (pk int, name VARCHAR(255))');
        $connection->insert('post', [
            'id' => 1,
            'user_id' => 1,
            'title' => 'Hello World',
            'content' => 'Some text',
        ]);
        $connection->insert('post', [
            'id' => 2,
            'user_id' => 1,
            'title' => 'Next thing',
            'content' => 'More text to read'
        ]);
        $connection->insert('user', [
            'pk' => 1,
            'name' => 'Franz'
        ]);
    }

    protected function tearDown()
    {
        $connection = ConnectionFacade::getConnection(ConnectionCollectionInterface::DEFAULT_CONNECTION);
        $connection->exec('DROP TABLE post');
        $connection->exec('DROP TABLE user');

        ConnectionFacade::__destruct();
    }

    public function testImplementsRepositoryInterface(){
        $this->assertTrue(is_subclass_of(PostRepository::class, RepositoryInterface::class));
    }

    public function testImplementsEntityAwareInterface(){
        $this->assertTrue(is_subclass_of(PostRepository::class, EntityAwareInterface::class));
    }

    public function testFind(){
        $post = (new PostRepository())->find(1);

        $this->assertInstanceOf(Post::class, $post);
        $this->assertEquals($post->get('id'), 1);
    }

    /**
     * find all
     */
    public function testAll()
    {
        $posts = (new PostRepository())->all();

        $this->assertInstanceOf(DataObject::class, $posts);
        $this->assertNotInstanceOf(Post::class, $posts);
    }

    public function testSaveNewObject(){
        $post = new Post();
        $post->set('title', 'My very new Title');
        $post->set('content', 'the content!');

        $repository = new PostRepository();
        $result = $repository->save($post);

        $this->assertEquals(1, $result);
    }

    public function testSaveNewDataArray(){
        $repository = new PostRepository();
        $result = $repository->save([
            'title' => 'My very new Title',
            'content' => 'the content!'
        ]);

        $this->assertEquals(1, $result);
    }

    public function testSaveExistingObject(){

        $repository = new PostRepository();
        $post = $repository->find(1);
        $post->set('title', 'My very new Title');
        $result = $repository->save($post);

        $this->assertEquals(1, $result);
    }

}
