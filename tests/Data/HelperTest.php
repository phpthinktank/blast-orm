<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 12.02.2016
 * Time: 14:06
 *
 */

namespace Blast\Tests\Db\Data;


use Blast\Db\Data\DataObject;
use Blast\Db\Data\DataHelper;
use Blast\Db\Data\ImmutableDataObject;
use Blast\Tests\Db\Stubs\Data\ArrayObject;
use Blast\Tests\Db\Stubs\Data\PlainObject;

/**
 * @coversDefaultClass \Blast\Db\Data\Helper
 */
class HelperTest extends \PHPUnit_Framework_TestCase
{
    public function testHelperForDataObject()
    {
        $config = [
            'class' => __CLASS__
        ];
        $object = new DataObject();
        $object->setData($config);

        $this->assertArrayHasKey('class', DataHelper::receiveDataFromObject($object));

        $config['method'] = __METHOD__;

        DataHelper::replaceDataFromObject($object, $config);

        $this->assertArrayHasKey('method', DataHelper::receiveDataFromObject($object));
        $this->assertArrayHasKey('method', $object->getData());
    }

    public function testHelperForImmutableDataObject()
    {
        $config = [
            'class' => __CLASS__
        ];
        $object = new DataObject();
        $object->setData($config);

        $this->assertArrayHasKey('class', DataHelper::receiveDataFromObject($object));
    }

    public function testThrowExceptionOnReplacingDataInImmutableDataObject()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        $config = [
            'class' => __CLASS__
        ];
        $object = new ImmutableDataObject();

        $config['method'] = __METHOD__;

        DataHelper::replaceDataFromObject($object, $config);
    }

    public function testHelperForArrayObject()
    {
        $config = [
            'class' => __CLASS__
        ];

        $object = new ArrayObject($config);

        $this->assertArrayHasKey('class', DataHelper::receiveDataFromObject($object));

        $config['method'] = __METHOD__;

        DataHelper::replaceDataFromObject($object, $config);

        $this->assertArrayHasKey('method', DataHelper::receiveDataFromObject($object));
        $this->assertArrayHasKey('method', $object->getArrayCopy());
    }

    public function testHelperForPlainObject()
    {
        $config = [
            'class' => __CLASS__
        ];

        $object = new PlainObject();

        foreach($config as $key => $value){
            $object->$key = $value;
        }

        $this->assertArrayHasKey('class', DataHelper::receiveDataFromObject($object));

        $config['method'] = __METHOD__;

        DataHelper::replaceDataFromObject($object, $config);

        $this->assertArrayHasKey('method', DataHelper::receiveDataFromObject($object));
        $this->assertTrue(property_exists($object, 'method'));

    }
}
