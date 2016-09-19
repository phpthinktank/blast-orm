<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 19.09.2016
 * Time: 15:46
 */

namespace Blast\Orm\Locator;


trait LocatorAwareTrait
{

    private $locator = null;

    public function getLocator(){
        if(null === $this->locator){
            $this->locator = new Locator();
        }

        return $this->locator;
    }

}
