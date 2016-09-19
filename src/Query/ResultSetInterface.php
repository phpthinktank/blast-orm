<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 17.09.2016
 * Time: 10:26
 */

namespace Blast\Orm\Query;


use Blast\Orm\Query;

interface ResultSetInterface extends \Countable, \IteratorAggregate
{

    /**
     * ResultSetInterface constructor.
     * @param $name
     * @param $query
     * @param $results
     */
    public function __construct($name, Query $query, array $results = []);

}
