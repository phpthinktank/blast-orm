<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 02.03.2016
 * Time: 15:58
 *
 */

namespace Blast\Orm\Container;


class Definition implements DefinitionInterface
{

    /**
     * @var string
     */
    private $id;
    /**
     * @var object
     */
    private $service;

    /**
     * @var \ReflectionObject
     */
    private $reflection;

    /**
     * @var array[]
     */
    private $methods = [];

    /**
     * @var bool
     */
    private $singleton = false;

    public function __construct($id, $service)
    {
        if($service === null){
            $service = $id;
        }

        if(is_object($id)){
            $id = get_class($id);
        }

        if(!is_string($id)){
            throw new ContainerException(sprintf('Id needs to be a string %s given.', gettype($id)));
        }

        $this->id = $id;
        $this->service = $service;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return object
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @return \ReflectionObject
     */
    public function getReflection()
    {
        if($this->reflection === null){
            $service = $this->getService();
            $this->reflection = is_string($service) ?
                new \ReflectionClass($service) :
                new \ReflectionObject($service);
        }
        return $this->reflection;
    }

    /**
     * @param array $args
     * @return mixed
     */
    public function invoke(array $args = [])
    {

        $reflection = $this->getReflection();
        $id = $this->getId();

        //validate contract
        if(interface_exists($id)){
            if(!$reflection->implementsInterface($id)){
                throw new DefinitionException(sprintf('%s needs to match contract by interface %s', $reflection->getName(), $id));
            }
        }

        if(class_exists($id) && $reflection->getName() != $id){
            if(!$reflection->isSubclassOf($id)){
                throw new DefinitionException(sprintf('%s needs to match contract by class %s', $reflection->getName(), $id));
            }
        }

        //create instance
        $instance = ($this->isSingleton()) ? $this->getService() : $reflection->newInstanceArgs($args);

        //invoke methods
        foreach($this->methods as $method => $argsCollection){
            if(!$reflection->hasMethod($method)){
                continue;
            }

            foreach($argsCollection as $args){
                $reflection->getMethod($method)->invokeArgs($instance, $args);
            }
        }

        return $instance;

    }

    /**
     * Call methods while invoke
     *
     * @param $method
     * @param array $args
     * @return $this
     */
    public function addMethodCall($method, array $args = [])
    {
        $this->methods[$method][] = $args;

        return $this;
    }

    /**
     * Remove method call
     *
     * @param $method
     * @return $this
     */
    public function removeMethodCall($method)
    {
        if(isset($this->methods[$method])){
            unset($this->methods[$method]);
        }

        return $this;
    }

    /**
     * @return boolean
     */
    public function isSingleton()
    {
        return $this->singleton;
    }

    /**
     * @param boolean $singleton
     * @return $this
     */
    public function setIsSingleton($singleton)
    {
        $this->singleton = $singleton;

        return $this;
    }
}