<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 11.02.2016
 * Time: 15:09
 *
 */

namespace Blast\Tests\Orm;


use Blast\Orm\Hook;
use Stubs\HookSubject;

class HookTest extends \PHPUnit_Framework_TestCase
{


    /**
     * Test that hook triggers methods from subject object
     */
    public function testTriggerHookFromSubjectObject()
    {

        $config = [
            'environment' => 'test'
        ];

        $subject = new HookSubject();

        $immutableValue = 'Any value';
        $subject->immutable = $immutableValue;


        $result = Hook::trigger('init', $subject, $config);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('services', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('immutable', $result);
        $this->assertArrayHasKey('environment', $result);
        $this->assertEquals($immutableValue, $result['immutable']);
        $this->assertArrayNotHasKey('wrong', $result);
    }

    /**
     * Test that hook triggers methods from subject object
     */
    public function testTriggerUnknownHookFromSubjectObject()
    {

        $config = [
            'environment' => 'test'
        ];

        $subject = new HookSubject();

        $immutableValue = 'Any value';
        $subject->immutable = $immutableValue;


        $result = Hook::trigger('setup', $subject, $config);
        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertArrayHasKey('environment', $result);
        $this->assertArrayNotHasKey('wrong', $result);
    }

    /**
     * Test that hook triggers methods from subject class
     */
    public function testTriggerHookFromSubjectClass()
    {

        $config = [
            'environment' => 'test'
        ];

        $result = Hook::trigger('init', HookSubject::class, $config);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('services', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('environment', $result);
        $this->assertArrayNotHasKey('wrong', $result);
    }

    /**
     * Test that hook returning all results
     */
    public function testTriggerHookGetAllResults()
    {

        $config = [
            'environment' => 'test'
        ];

        $result = Hook::trigger('init', HookSubject::class, $config, Hook::HOOK_ALL_RESULTS);
        $this->assertInternalType('array', $result);
        $this->assertCount(3, $result);
        $this->assertArrayHasKey('initConfig', $result);
        $this->assertArrayHasKey('initServices', $result);
        $this->assertArrayHasKey('initImmutable', $result);
        $this->assertArrayNotHasKey('doSomethingWrong', $result);
    }

    /**
     * Test that hook executing explicit
     */
    public function testTriggerHookGetExplicit()
    {
        $config = [
            'environment' => 'test'
        ];

        $result = Hook::trigger('initConfig', HookSubject::class, $config, Hook::HOOK_EXPLICIT);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('environment', $result);
        $this->assertArrayNotHasKey('wrong', $result);
    }

    /**
     * Test that hook executing explicit and returning all results
     */
    public function testTriggerHookGetExplicitAndAllResults()
    {
        $config = [
            'environment' => 'test'
        ];

        $result = Hook::trigger('initConfig', HookSubject::class, $config, Hook::HOOK_EXPLICIT|Hook::HOOK_ALL_RESULTS);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('initConfig', $result);
        $this->assertArrayNotHasKey('doSomethingWrong', $result);
    }

    /**
     * Test wrong name type
     */
    public function testInvalidName(){
        $this->setExpectedException(\InvalidArgumentException::class);
        Hook::trigger([], new HookSubject(), []);
    }

    /**
     * Test wrong subject type
     */
    public function testInvalidSubject(){
        $this->setExpectedException(\InvalidArgumentException::class);
        Hook::trigger('init', []);
    }
}
