<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 01.02.2016
 * Time: 15:18
 *
 */

namespace Blast\Db\Orm\Relations;


use Blast\Db\Data\DataHelper;
use Blast\Db\Entity\EntityInterface;
use Blast\Db\Orm\Factory;
use Blast\Db\Orm\Model\ModelInterface;
use Blast\Db\Query;
use Blast\Db\Query\Result;
use Blast\Db\Query\ResultCollection;

class BelongsTo extends AbstractRelation
{
    /**
     * BelongsTo constructor.
     * @param EntityInterface $model current entity instance
     * @param string|EntityInterface $foreignEntity Entity which have a key field in current entity
     * @param string|integer|null $localKey Field name on current entity which matches up with foreign key of foreign entity
     */
    public function __construct(EntityInterface $model, $foreignEntity, $localKey = null)
    {
        //local config
        $this->entity = $model;
        $this->localKey = $localKey;

        //foreign config
        $this->foreignEntity = $foreignEntity;
    }

    /**
     * Save foreign entity and store value of foreign key into local key field
     * @return ModelInterface
     */
    public function save()
    {
        $foreignEntity = $this->getForeignEntity();
        $foreignData = DataHelper::receiveDataFromObject($foreignEntity);
        $entity = $this->getEntity();
        $data = DataHelper::receiveDataFromObject($entity);
        $data[$this->getLocalKey()] = $foreignData[$this->getForeignKey()];
        DataHelper::replaceDataFromObject($entity,$data);

        Factory::createMapper($foreignEntity)->save($this->getResults());

        return $foreignEntity;
    }

    /**
     * Fetch data from foreign entity, when value of foreign key matches up with value of local key
     *
     * @return ModelInterface|Result|ResultCollection
     */
    public function fetch()
    {
        $data = DataHelper::receiveDataFromObject($this->getEntity());
        $result = Factory::createMapper($this->getForeignEntity())->find($data[$this->getLocalKey()], $this->getForeignKey());

        return $this->setResults($result)->getResults();
    }

}