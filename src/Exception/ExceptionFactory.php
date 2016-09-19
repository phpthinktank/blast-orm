<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 19.09.2016
 * Time: 12:09
 */

namespace Blast\Orm\Exception;


use Blast\Orm\Query;
use Blast\Orm\Support;

class ExceptionFactory
{

    /**
     * @param $current
     * @param $expected
     * @return \InvalidArgumentException
     */
    public static function invalidClassInstanceException($current, $expected){
        return new \InvalidArgumentException(sprintf('%s needs to be an instance of %s', Support::getClass($current), Support::getClass($expected)));
    }

    /**
     * @param string $on before or after
     * @param Query $rawQuery
     * @return \InvalidArgumentException
     */
    public static function queryCanceledException($on, Query $rawQuery){
        return new \InvalidArgumentException(sprintf("Query has been canceled %s execution.\n With Query: %s", $on, $rawQuery->getSQL()));
    }

}
