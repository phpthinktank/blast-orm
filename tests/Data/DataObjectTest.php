<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 12.02.2016
 * Time: 13:18
 *
 */

namespace Blast\Tests\Db\Data;

use Blast\Tests\Db\Stubs\Data\DataObjectImpl;

class DataObjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Blast\Db\Data\AccessorTrait
     *
     */
    public function testAccessingData()
    {
        $data = new DataObjectImpl();
        $data->setData([
            'class' => __CLASS__
        ]);

        $this->assertTrue($data->has('class'));
        $this->assertTrue(isset($data->class));
        $this->assertEquals(__CLASS__, $data->get('class'));
        $this->assertEquals(__CLASS__, $data->class);

        $this->assertFalse($data->has('method'));
        $this->assertFalse(isset($data->method));
        $this->assertEquals(__METHOD__, $data->get('method', __METHOD__));
        $this->assertEquals(null, $data->method);

    }

    /**
     * @covers Blast\Db\Data\ConverterTrait
     */
    public function testConvertData()
    {
        $data = new DataObjectImpl();
        $data->setData([
            'services.core' => 'com.core',
            'services.core.validate' => 'com.core.validate',
            'services.cms' => 'com.cms',
        ]);

        $this->assertInternalType('array', $data->toArray());
        $this->assertJson($data->toJson());
    }

    /**
     * @covers Blast\Db\Data\FilterTrait
     */
    public function testFilterData()
    {
        $data = new DataObjectImpl();
        $data->setData([
            'services.core' => 'com.core',
            'services.core.validate' => 'com.core.validate',
            'services.cms' => 'com.cms',
        ]);

        //filter for core components
        $coreComponents = $data->filter(function($key, $value){
            return strpos($key, 'services.core') === 0;
        });

        $this->assertArrayHasKey('services.core', $coreComponents);
        $this->assertArrayHasKey('services.core.validate', $coreComponents);
        $this->assertArrayNotHasKey('services.cms', $coreComponents);

    }

    /**
     * @covers Blast\Db\Data\MutatorTrait
     */
    public function testMutatingData()
    {
        $data = new DataObjectImpl();
        $data->setData([
            'class' => __CLASS__
        ]);

        $this->assertArrayHasKey('class', $data->getData());
        $data->set('method', __METHOD__);
        $data->method = __METHOD__;
        $this->assertArrayHasKey('method', $data->getData());

        $data->remove('method');
        $this->assertArrayNotHasKey('method', $data->getData());

        $data->set('method', __METHOD__);
        unset($data->method);
        $this->assertArrayNotHasKey('method', $data->getData());

    }

}
