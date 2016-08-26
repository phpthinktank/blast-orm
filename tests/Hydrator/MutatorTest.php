<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 26.08.2016
 * Time: 09:45
 */

namespace Blast\Tests\Orm\Hydrator;


use Blast\Orm\Entity\Provider;
use Blast\Orm\Hydrator\EntityHydrator;
use Blast\Tests\Orm\Stubs\Entities\Address;

class MutatorTest extends \PHPUnit_Framework_TestCase
{

    public function testUnderScoreCamelcaseMutator(){
        $provider = new Provider(Address::class);
        $hydrator = new EntityHydrator($provider);

        $data = [
            'id' => 200,
            'user_id' => 800,
            'address' => 'my address',
            'full_name' => 'my name',
        ];

        $entity = $hydrator->hydrate($data, EntityHydrator::HYDRATE_ENTITY);

        $this->assertEquals($data['user_id'], $entity->getUserId());
        $this->assertEquals($data['full_name'], $entity->getFullName());
    }

    public function testUnderScoreCamelcaseAccessor(){
        $entity = new Address();
        $entity->setId(200);
        $entity->setUserId(800);
        $entity->setAddress('my address');
        $entity->setFullName('my name');

        $provider = new Provider($entity);
        $hydrator = new EntityHydrator($provider);

        $data = $hydrator->extract();

        $this->assertEquals($entity->getUserId(), $data['userId']);
        $this->assertEquals($entity->getFullName(), $data['fullName']);
    }

}
