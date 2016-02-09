<?php
/*
*
* (c) Marco Bunge <marco_bunge@web.de>
*
* For the full copyright and license information, please view the LICENSE.txt
* file that was distributed with this source code.
*
* Date: 08.02.2016
* Time: 15:01
*/

namespace Blast\Db\Entity;


interface CollectionInterface extends \Iterator
{

    /**
     * EntityCollectionInterface constructor.
     * @param EntityInterface[] $data
     */
    public function __construct(array $data = []);

    /**
     * get count of data
     *
     * @return int
     */
    public function count();

    /**
     * Filter containing entities
     *
     * @param callable $filter
     * @return EntityInterface[]
     */
    public function filter(callable $filter);

    /**
     * Return data. If with has been set, relations will be attached
     * @return EntityInterface[]
     */
    public function getData();

}