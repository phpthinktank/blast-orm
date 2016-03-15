<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 25.01.2016
 * Time: 16:08
 *
 */

namespace Blast\Orm\Query\Events;


use Blast\Orm\Query;

class QueryBuilderEvent extends AbstractQueryEvent
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Query
     */
    private $builder;

    /**
     * ResultEvent constructor.
     * @param string $name
     * @param Query $builder
     */
    public function __construct($name, $builder)
    {
        $this->name = $name;
        $this->builder = $builder;
    }

    /**
     * Return event name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Query
     */
    public function getBuilder()
    {
        return $this->builder;
    }


}
