<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 20.07.2016
 * Time: 09:07
 *
 */

namespace Blast\Orm\Entity\Converter;
use Doctrine\DBAL\Types\Type;

/**
 * Interface Converter
 * @package Blast\Orm\Entity\Converter
 *
 * The converter could be passed to a connection or query object and allows custom converting between
 * php and db values and vice versa for a single type.
 */
interface Converter
{

    /**
     * Converter constructor.
     * @param Type $type
     */
    public function __construct(Type $type);

    /**
     * Convert a database value to a PHP value
     *
     * @param mixed $value
     * @param array $options An array of custom options for a specific converter
     * @return mixed The converted PHP value
     */
    public function toPhpValue($value, array $options = []);

    /**
     * Convert a PHP value to a database value
     *
     * @param mixed $value
     * @param array $options An array of custom options for a specific converter
     * @return mixed The converted database value
     */
    public function toDatabaseValue($value, array $options = []);

    /**
     * The given type for converter
     * @return mixed
     */
    public function getType();

}
