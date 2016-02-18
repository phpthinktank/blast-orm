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


use Blast\Db\Data\DataHelper;
use Blast\Db\Orm\Model\ModelInterface;
use Doctrine\DBAL\Driver\Statement;
use stdClass;

class ResultDecorator
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
    const RESULT_RAW = 'raw';
    /**
     *
     */
    const RESULT_AUTO = 'auto';

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
    public function __construct($data = [], $entity = null)
    {
        $this->data = $data;
        $this->entity = $entity;
    }

    /**
     * Determine result and return one or many results
     *
     * @param string $convert
     * @return array|Result|ResultCollection|ModelInterface|stdClass|\ArrayObject
     */
    public function decorate($convert = self::RESULT_AUTO)
    {

        $data = $this->data;
        if($this->isRaw($convert)){
            return $data;
        }

        $count = count($data);
        $entity = NULL;

        if ($count > 1 || $convert === static::RESULT_COLLECTION) { //if entity set has many items, return a collection of entities
            foreach ($data as $key => $value) {
                $data[ $key ] = $this->newModel($value);
            }
            $entity = new ResultCollection($data);
        } elseif ($count === 1 || $convert === static::RESULT_ENTITY) { //if entity has one item, return the entity
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
        if(!is_object($entity)){
            $entity = new Result();
        }

        DataHelper::replaceDataFromObject($entity, $data);

        return $entity;
    }

    /**
     * @param $convert
     * @return bool
     */
    public function isRaw($convert)
    {
        return $convert === self::RESULT_RAW ||
        $this->data instanceof Statement ||
        $this->entity === NULL ||
        is_int($this->data);
    }

}