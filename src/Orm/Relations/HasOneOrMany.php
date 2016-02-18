<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 02.02.2016
 * Time: 15:56
 *
 */

namespace Blast\Db\Orm\Relations;


use Blast\Db\Data\DataHelper;
use Blast\Db\Orm\Factory;
use Blast\Db\Orm\Model\ModelInterface;
use Blast\Db\Query;

class HasOneOrMany extends AbstractRelation
{

    /**
     * BelongsTo constructor.
     * @param ModelInterface $model current entity instance
     * @param string|ModelInterface $foreignEntity Entity which have a key field in current entity
     * @param string|integer|null $localKey Field name on current entity which matches up with foreign key of foreign entity
     * @param string|integer|null $foreignKey name on foreign entity which matches up with local key of current entity
     */
    public function __construct(ModelInterface $model, $foreignEntity, $localKey = null, $foreignKey = null)
    {
        //local config
        $this->entity = $model;
        $this->localKey = $localKey;

        //foreign config
        $this->foreignEntity = $foreignEntity;
        $this->foreignKey = $foreignKey;
    }

    /**
     * @todo Entity should be able to save many entities
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
     * @return Query\ResultCollection
     */
    public function fetch()
    {
        $data = DataHelper::receiveDataFromObject($this->getEntity());
        $result = Factory::createMapper($this->getForeignEntity())
            ->find($data[$this->getLocalKey()], $this->getForeignKey());

        return $this->setResults($result)->getResults();
    }

}