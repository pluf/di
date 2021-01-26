<?php
namespace Pluf\Di;

use Pluf\Di\ParameterResolver\ParameterResolver;
use Pluf\Di\ParameterResolver\ResolverChain;
use Psr\Container\ContainerInterface;

/**
 * Builder for Container
 *
 * There are many options to create new instance of a container. This class encapsolate the
 * complexity of creating new instance of Container.
 *
 * For example, adding resolvers, enabling cache and setting parent container is encapsolated
 * into the builder.
 *
 * @author Mostafa Barmshory<mostafa.barmshory@gmail.com>
 *        
 */
class ContainerBuilder
{

    /**
     * List of all registerd resolver
     *
     * @var array
     */
    private array $resolvers = [];

    /**
     * Parent container
     *
     * @var ContainerInterface
     */
    private ?ContainerInterface $parent = null;

    /**
     * Adds new resolver into the resolvers list
     *
     * The new resolver will be added at the end of the list.
     *
     * The first one is the most important
     *
     * @param ParameterResolver $resolver
     *            to add to the list
     * @return ContainerBuilder the builder instance
     */
    public function addResolver(ParameterResolver $resolver): ContainerBuilder
    {
        $this->resolvers[] = $resolver;
        return $this;
    }

    /**
     * Sets the parent container
     *
     * @param ContainerInterface $parent
     *            the parent container
     * @return ContainerBuilder the builder
     */
    public function setParent(?ContainerInterface $parent = null): ContainerBuilder
    {
        $this->parent = $parent;
        return $this;
    }

    public function enableCompilation(string $path): ContainerBuilder
    {
        return $this;
    }

    public function writeProxiesToFile(bool $enable, ?string $path = '/tmp/plfu_di_'): ContainerBuilder
    {
        return $this;
    }

    public function useAutowiring(bool $enable): ContainerBuilder
    {
        return $this;
    }

    public function useAnnotations(bool $enable): ContainerBuilder
    {
        return $this;
    }

    /**
     * Creates new instance of container
     *
     * @return Container
     */
    public function build(): Container
    {
        $invoker = null;
        if (sizeof($this->resolvers) > 0) {
            $invoker = new Invoker(new ResolverChain($this->resolvers));
        }
        return new Container($this->parent, $invoker);
    }
}

