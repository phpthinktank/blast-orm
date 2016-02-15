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
     * @var array|\ArrayObject|ModelInterface|null|stdClass
     */
    private $model;

    /**
     * ResultDecorator constructor.
     * @param array $data
     * @param ModelInterface|array|stdClass|\ArrayObject $model
     */
    public function __construct($data = [], $model = null)
    {
        $this->data = $data;
        $this->model = $model;
    }

    /**
     * Determine result and return one or many results
     *
     * @param string $convert
     * @return array|Result|ResultCollection
     */
    public function decorate($convert = self::RESULT_AUTO)
    {

        $data = $this->data;
        if($this->isRaw($convert)){
            return $data;
        }

        $count = count($data);
        $model = NULL;

        if ($count > 1 || $convert === static::RESULT_COLLECTION) { //if model set has many items, return a collection of entities
            foreach ($data as $key => $value) {
                $data[ $key ] = $this->newModel($value);
            }
            $model = new ResultCollection($data);
        } elseif ($count === 1 || $convert === static::RESULT_ENTITY) { //if model has one item, return the entity
            $model = $this->newModel(array_shift($data));
        }

        return $model;
    }

    /**
     * @param array $data
     * @return Result
     */
    protected function newModel($data = [])
    {
        $model = new Result();
        $model->setData($data);

        return $model;
    }

    /**
     * @param $convert
     * @return bool
     */
    public function isRaw($convert)
    {
        return $convert === self::RESULT_RAW ||
        $this->data instanceof Statement ||
        $this->model === NULL ||
        is_int($this->data);
    }

}