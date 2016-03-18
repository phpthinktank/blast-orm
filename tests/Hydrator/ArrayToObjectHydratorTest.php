<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 18.03.2016
 * Time: 15:57
 *
 */

namespace Blast\Tests\Orm\Hydrator;


use Blast\Orm\Entity\Provider;
use Blast\Tests\Orm\Stubs\Entities\User;

class ArrayToObjectHydratorTest extends \PHPUnit_Framework_TestCase
{


    public function testHydrateToPlainObject()
    {
        $entity = User::class;
        $data = [
            'pk' => 1,
            'name' => 'Gunther'
        ];
        $provider = new Provider($entity);

        $entityClone = $provider->withData($data);

        $this->assertEquals($data['pk'], $entityClone->getPk());
        $this->assertEquals($data['name'], $entityClone->getName());

    }
}
