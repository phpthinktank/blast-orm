<?php
/*
*
* (c) Marco Bunge <marco_bunge@web.de>
*
* For the full copyright and license information, please view the LICENSE.txt
* file that was distributed with this source code.
*
* Date: 08.02.2016
* Time: 15:08
*/

namespace Blast\Db\Entity;



use Blast\Db\DataConverterTrait;

class Collection implements CollectionInterface
{
    use DataConverterTrait;

    /**
     * @var EntityInterface[]
     */
    private $data = [];

    /**
     * EntityCollectionInterface constructor.
     * @param EntityInterface[] $data
     */
    public function __construct(array $data = [])
    {
        //check data and remove when value is no valid entity
        foreach($data as $key => $value){
            if(!($value instanceof EntityInterface)){
                unset($data[$key]);
            }
        }
        $this->data = $data;
    }

    /**
     * reset pointer on data
     */
    public function rewind() {
        $data = $this->getData();
        reset($data);
    }

    /**
     * Get current entity
     * @return EntityInterface
     */
    public function current() {
        $data = $this->getData();
        return current($data);
    }

    /**
     * Get current position
     * @return string|int
     */
    public function key() {
        $data = $this->getData();
        return key($data);
    }

    /**
     * Set pointer to next entity and return entity
     * @return mixed
     */
    public function next() {
        $data = $this->getData();
        return next($data);
    }

    /**
     * check if current value is valid
     * @return bool
     */
    public function valid() {
        return $this->current() !== false;
    }

    /**
     * get count of data
     *
     * @return int
     */
    public function count()
    {
        return count($this->getData());
    }

    /**
     * Filter containing entities
     *
     * @param callable $filter
     * @return EntityInterface[]
     */
    public function filter(callable $filter)
    {
        return array_filter($this->getData(), $filter, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Return data. If with has been set, relations will be attached
     * @return EntityInterface[]
     */
    public function getData()
    {
        return $this->data;
    }
}