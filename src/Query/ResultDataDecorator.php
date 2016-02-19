<?php
/*
*
* (c) Marco Bunge <marco_bunge@web.de>
*
* For the full copyright and license information, please view the LICENSE.txt
* file that was distributed with this source code.
*
* Date: 15.02.2016
* Time: 22:55
*/

namespace Blast\Db\Query;


use Blast\Db\Data\DataDecoratorInterface;
use Blast\Db\Data\DataHelper;
use Blast\Db\Orm\Model\ModelInterface;
use Doctrine\DBAL\Driver\Statement;
use stdClass;

class ResultDataDecorator implements DataDecoratorInterface
{

    /**
     *
     */
    const RESULT_COLLECTION = 'collection';
    /**
     *
     */
    const RESULT_ENTITY = 'entity';

    /**
     *
     */
    const RAW = 'raw';

    /**
     * @var array
     */
    private $data;
    /**
     * @var array|\ArrayObject|ModelInterface|null|stdClass|Result
     */
    private $entity;

    /**
     * ResultDecorator constructor.
     * @param array $data
     * @param ModelInterface|array|stdClass|\ArrayObject $entity
     */
    public function __construct($data = [], $entity = NULL)
    {
        $this->setData($data);
        $this->setEntity($entity);
    }

    /**
     * @return array|\ArrayObject|ModelInterface|Result|null|stdClass
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param array|\ArrayObject|ModelInterface|Result|null|stdClass $entity
     * @return $this
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data = [])
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Determine result and return one or many results
     *
     * @param string $option
     * @return array|Result|ResultCollection|ModelInterface|stdClass|\ArrayObject
     */
    public function decorate($option = self::AUTO)
    {
        $data = $this->data;
        if ($this->isRaw($option)) {
            return $data;
        }

        $count = count($data);
        $entity = NULL;

        if ($count > 1 || $option === static::RESULT_COLLECTION) { //if entity set has many items, return a collection of entities
            foreach ($data as $key => $value) {
                $data[ $key ] = $this->newModel($value);
            }
            $entity = new ResultCollection($data);
        } elseif ($count === 1 || $option === static::RESULT_ENTITY) { //if entity has one item, return the entity
            $entity = $this->newModel(array_shift($data));
        }

        return $entity;
    }

    /**
     * Pass data to result or model
     * @param array $data
     * @return Result
     */
    protected function newModel($data = [])
    {
        $entity = $this->entity;
        if (!is_object($entity)) {
            $entity = new Result();
        }

        DataHelper::replaceDataFromObject($entity, $data);

        return $entity;
    }

    /**
     * @param $option
     * @return bool
     */
    public function isRaw($option)
    {
        return $option === self::RAW ||
        $this->data instanceof Statement ||
        $this->entity === NULL ||
        is_int($this->data);
    }

}