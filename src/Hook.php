<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 11.02.2016
 * Time: 15:07
 *
 */

namespace Blast\Db;


class Hook
{
    const HOOK_EXPLICIT = 1;
    const HOOK_ALL_RESULTS = 2;

    /**
     * Execute hook by name. To hook into a subject you need to create a method prefixed with hook name.
     *
     * `Factory::triggerHook(new Object, 'do')` is triggering all method prefixed with "do". e.g. doFetch, doDelete,
     * etc.
     *
     * If explicit is set to true only method with hook name will be executed
     *
     * @param string $name
     * @param object $subject
     * @param array $params
     * @param int $options
     * @return array|mixed
     */
    public static function trigger($name, $subject, array $params = [], $options = 0){

        if(!is_string($name)){
            throw new \InvalidArgumentException('Hook name needs to be a string. ' . gettype($subject) . ' given.');
        }

        if(!is_object($subject)){
            if(!(is_string($subject) && class_exists($subject))){
                throw new \InvalidArgumentException('Subject of hook ' . $name . ' needs to be a valid class. ' . gettype($subject) . ' given.');
            }
        }

        $reflection = new \ReflectionClass($subject);

        if(is_string($subject)){
            $subject = $reflection->newInstanceWithoutConstructor();
        }

        $results = [];

        foreach($reflection->getMethods() as $method){
            $methodName = $method->getName();

            //filter all hooks which does not match with $name as prefix
            if($options & static::HOOK_EXPLICIT ? $name !== $methodName : strpos($methodName, $name) !== 0){
                continue;
            }

            //all methods need to be accessible before invoke
            if($method->isPrivate() || $method->isProtected()){
                $method->setAccessible(true);
            }

            $result = $method->invoke($subject, $params);

            //attach params only if result is an array
            //this avoids a void return
            $params = is_array($result) ? $result : $params;

            if($options & static::HOOK_ALL_RESULTS){
                $results[$methodName] = $result;
            }
        }

        return $options & static::HOOK_ALL_RESULTS ? $results : $params;
    }
}