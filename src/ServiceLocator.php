<?php
namespace Pluf\Di;

use Pluf\Di\Exception\UnknownIdentifierException;
use Psr\Container\ContainerInterface;

/**
 * PSR-11 service locator.
 *
 * @author Mostafa Barmshory<mostafa.barmshory@gmail.com>
 */
class ServiceLocator implements ContainerInterface
{

    private $container;

    private $aliases = [];

    /**
     *
     * @param Container $container
     *            The Container instance used to locate services
     * @param array $ids
     *            Array of service ids that can be located. String keys can be used to define aliases
     */
    public function __construct(Container $container, array $ids)
    {
        $this->container = $container;

        foreach ($ids as $key => $id) {
            $this->aliases[\is_int($key) ? $id : $key] = $id;
        }
    }

    /**
     *
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (! isset($this->aliases[$id])) {
            throw new UnknownIdentifierException($id);
        }

        return $this->container[$this->aliases[$id]];
    }

    /**
     *
     * {@inheritdoc}
     */
    public function has($id)
    {
        return isset($this->aliases[$id]) && isset($this->container[$this->aliases[$id]]);
    }
}
