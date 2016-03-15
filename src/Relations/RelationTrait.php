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
    public function getQuery()
    {
        $this->init();
        return $this->query;
    }

    /**
     * Placeholder init function
     *
     * @codeCoverageIgnore
     */
    protected function init()
    {

    }

    /**
     * @return string
     */
    public function getName()
    {
        $this->init();
        return $this->name;
    }

}
