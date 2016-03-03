<?php
/**
 *
 * (c) Marco Bunge <marco_bunge@web.de>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Date: 02.03.2016
 * Time: 15:48
 *
 */

namespace Blast\Orm\Container;


use Interop\Container\ContainerInterface;

class Container implements ContainerInterface
{

    /**
     * @var DefinitionInterface[]
     */
    private $definitions = [];

    /**
     * Add an entry to the container by its identifier
     *
     * @param $id
     * @param $service
     * @param bool $singleton
     *
     * @return DefinitionInterface
     */
    public function add($id, $service = NULL, $singleton = FALSE)
    {

        $definition = (new Definition($id, $service))->setIsSingleton($singleton);
        $id = $definition->getId();
        $this->definitions[ $id ] = $definition;

        return $this->getDefinition($id);
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @param array $args
     * @return mixed No entry was found for this identifier.
     * @throws DefinitionNotFoundException
     */
    public function get($id, array $args = [])
    {
        $service = NULL;
        if (is_object($id)) {
            $service = $id;
            $id = get_class($id);
        }
        if (!is_string($id)) {
            throw new ContainerException(sprintf('id needs to be a string, %s given.', gettype($id)));
        }

        $definition = class_exists($id) && !$this->has($id) ?
            $this->add($id, $service) :
            $this->getDefinition($id);

        return $definition->invoke($args);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return boolean
     */
    public function has($id)
    {
        return isset($this->definitions[ $id ]);
    }

    /**
     * @param $id
     * @return DefinitionInterface
     * @throws DefinitionNotFoundException
     */
    public function getDefinition($id)
    {
        if ($this->has($id)) {
            return $this->definitions[ $id ];
        }

        throw new DefinitionNotFoundException($id);
    }
}