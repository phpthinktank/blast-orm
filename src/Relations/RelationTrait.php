<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 29.02.2016
 * Time: 12:06
 *
 */

namespace Blast\Orm\Relations;


use Blast\Orm\Entity\ProviderFactoryInterface;
use Blast\Orm\Query;

trait RelationTrait
{

    /**
     * @var Query
     */
    protected $query = null;

    /**
     * @var string
     */
    protected $name = null;

    /**
     * Query for accessing related data
     *
     * @return Query
     */
    abstract public function getQuery();

    /**
     * Get local entity
     *
     * @return mixed
     */
    abstract protected function getEntity();

    /**
     * Get foreign entity
     *
     * @return mixed
     */
    abstract protected function getForeignEntity();

    /**
     * @return string
     */
    public function getName()
    {
        if($this instanceof ProviderFactoryInterface){
            $this->name = $this->createProvider($this->getForeignEntity())->getDefinition()->getTableName();
        }
        return $this->name;
    }

}
