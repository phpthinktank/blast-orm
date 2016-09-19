<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 19.09.2016
 * Time: 07:56
 */

namespace Blast\Orm\Query;


use Blast\Orm\Query;
use Traversable;

class ResultSet implements ResultSetInterface
{
    /**
     * @var
     */
    private $name;
    /**
     * @var Query
     */
    private $query;
    /**
     * @var
     */
    private $results;


    /**
     * ResultSetInterface constructor.
     * @param string $name
     * @param Query $query
     * @param array $results
     */
    public function __construct($name, Query $query, array $results = [])
    {

        $this->name = $name;
        $this->query = $query;
        $this->results = $results;
    }

    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return count($this->results);
    }

    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->results);
    }
}
