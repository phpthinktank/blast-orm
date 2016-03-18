<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 18.03.2016
 * Time: 08:09
 *
 */

namespace Blast\Orm;


use Blast\Orm\Entity\Provider;
use Doctrine\DBAL\Query\QueryBuilder;

class TransactionHistory implements TransactionHistoryInterface
{

    /**
     * @var \ArrayObject
     */
    private static $storage = null;

    /**
     * Push a new entry with entity, type and status by id
     *
     * @param string $id
     * @param object $entity
     * @param int $type
     * @param int $status
     * @param null $data
     * @return $this
     * @throws \Exception
     */
    public function store($id, $entity, $type, $status = self::PENDING, $data = null)
    {
        $typeName = $this->getTypeName($type);
        $provider = new Provider($entity);
        if (!in_array($status, [static::PENDING, static::PROCESS, static::COMPLETE])) {
            throw new \InvalidArgumentException('Invalid transaction status ' . (is_scalar($status) ? $status : gettype($status)));
        }
        $storage = $this->getStorage();
        $table = $provider->getTableName();
        $entry = $storage->offsetExists($table) ?
            $storage->offsetGet($table) :
            [];

        $entry[$id]['status'][$this->getStatusName($status)] = null !== $data ? $data : $entity;
        $entry[$id]['type'] = $typeName;
        $entry[$id]['reference'] = get_class($provider->getEntity());

        $storage->offsetSet($table, $entry);

        return $id;
    }

    /**
     * Get history entries filtered by type, status and or class name
     *
     * @param bool $className
     * @param bool $type
     * @param bool $status
     *
     * @return array
     */
    public function filter($className = false, $type = false, $status = false)
    {
        return $this->iterateStorageToDataArray(function (array $array, \ArrayIterator $storage) use ($type, $status, $className) {
            $current = $storage->current();
            $table = $storage->key();

            $typeName = $type !== false ? $this->getTypeName($type) : null;
            $statusName = $status !== false ? $this->getStatusName($status) : null;

            foreach ($current as $id => $item) {
                if (!($item['type'] === $typeName || !$type)) {
                    continue;
                }
                if (!(isset($item['status'][$statusName]) || !$status)) {
                    continue;
                }

                if (!($className === $item['reference'] || !$className)) {
                    continue;
                }

                $array[$table][$id] = $item;
            }

            return $array;
        });
    }

    /**
     * Get transaction type name
     * @param $type
     * @return string
     * @throws \Exception
     */
    public function getTypeName($type)
    {
        switch ($type) {
            case QueryBuilder::SELECT:
                return 'clean';
            case QueryBuilder::INSERT:
                return 'new';
            case QueryBuilder::UPDATE:
                return 'dirty';
            case QueryBuilder::DELETE:
                return 'removed';
            // @codeCoverageIgnoreStart
            default:
                //this could only happen if query will be extended and a custom getType is return invalid type
                throw new \Exception('Unknown transaction type ' . $type);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Get transaction type name
     * @param $status
     * @return string
     * @throws \Exception
     */
    public function getStatusName($status)
    {
        switch ($status) {
            case TransactionHistoryInterface::PENDING:
                return 'pending';
            case TransactionHistoryInterface::PROCESS:
                return 'process';
            case TransactionHistoryInterface::COMPLETE:
                return 'complete';
            // @codeCoverageIgnoreStart
            default:
                //this could only happen if query will be extended and a custom getType is return invalid type
                throw new \Exception('Unknown transaction status ' . $status);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Iterate and convert storage to data array
     *
     * @param callable|null $callback
     * @return array
     */
    private function iterateStorageToDataArray($callback = null)
    {
        $result = [];

        if (null === $callback) {
            $callback = function (array $array, \ArrayIterator $storage) {
                $array[$storage->key()] = $storage->current();
            };
        }

        $iterator = $this->getStorage()->getIterator();
        $iterator->rewind();

        while ($iterator->valid()) {
            $result = $callback($result, $iterator);
            $iterator->next();
        }

        return $result;
    }

    /**
     * Get the object storage
     *
     * @return \ArrayObject
     */
    public function getStorage()
    {
        if (static::$storage === null) {
            static::$storage = new \ArrayObject();
        }
        return static::$storage;
    }

    /**
     * Convert history to json when object converts to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Convert history into an array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->filter();
    }

    /**
     * Convert history to json
     *
     * @param int $options By default JSON_PRETTY_PRINT (128)
     * @return string
     */
    public function toJson($options = 128)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * @return string
     */
    public function uniqueId()
    {
        return md5(microtime() + rand(5, 461));
    }


}
