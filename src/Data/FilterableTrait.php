<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 12.02.2016
 * Time: 10:39
 *
 */

namespace Blast\Db\Data;


trait FilterableTrait
{
    /**
     * Filter data by callback
     *
     * Emulates array_filter behaviour with optional flags ARRAY_FILTER_USE_BOTH for PHP version < 5.6.x
     *
     * Create a callback with key and value parameters and return a boolean.
     *
     * ```
     * FilterableTrait::filter(function($key, $value){
     *  //added to result if value is scalar
     *  return is_scalar($value)
     * });
     * ```
     *
     * @see http://php.net/manual/de/function.array-filter.php
     *
     * @param callable $filter
     * @return array
     */
    public function filter(callable $filter)
    {
        $data = DataHelper::receiveDataFromObject($this);

        if(defined('ARRAY_FILTER_USE_BOTH') && version_compare(PHP_VERSION, '5.6.0') >= 0){
            return array_filter($data, $filter, ARRAY_FILTER_USE_BOTH);
        }

        $results = [];

        //if filter is truthy pass key-value-pair to results
        foreach($data as $key => $value){
            if(call_user_func($filter, $key, $value) == true){
                $results[$key] = $value;
            }
        }

        return $results;
    }
}