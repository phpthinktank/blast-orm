<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 02.03.2016
 * Time: 16:00
 *
 */

namespace Blast\Orm\Container;


interface DefinitionInterface
{

    /**
     * @param array $args
     * @return mixed
     */
    public function invoke(array $args = []);

    /**
     * Call methods while invoke
     *
     * @param $method
     * @param array $args
     * @return mixed
     */
    public function addMethodCall($method, array $args = []);

    /**
     * @param $singleton
     * @return $this
     */
    public function setIsSingleton($singleton);

}