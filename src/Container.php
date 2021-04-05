<?php
/*
 * Pluf, the light and fast PHP SaaS framework
 * Copyright (C) 2020 pluf.ir
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */
namespace Pluf\Di;

use Pluf\Di\Exception\ExpectedInvokableException;
use Pluf\Di\Exception\FrozenServiceException;
use Pluf\Di\Exception\UnknownIdentifierException;
use Pluf\Di\ParameterResolver\DefaultValueResolver;
use Pluf\Di\ParameterResolver\ResolverChain;
use Pluf\Di\ParameterResolver\Container\ParameterNameContainerResolver;
use Psr\Container\ContainerInterface;
use ArrayAccess;
use Closure;
use InvalidArgumentException;

/**
 * Container main class.
 *
 * @author Mostafa Barmshory<mostafa.barmshory@gmail.com>
 */
class Container implements ArrayAccess, ContainerInterface, InvokerInterface
{

    /**
     * List of factories
     *
     * @var array
     */
    private array $factories = [];

    /**
     * Frozes the factory
     *
     * If a factory used, then it is forbiden to override it.
     *
     * @var array
     */
    private array $frozen = [];

    /**
     * Parent container is used to resolve services hericically.
     *
     * @var Container
     */
    private ?Container $parent = null;

    private ?Container $root = null;

    /**
     * The invoker is used to call the factories
     *
     * @var Invoker
     */
    private ?Invoker $internalInvoker = null;

    /**
     * Instantiates the container.
     *
     * Objects and parameters can be passed as argument to the constructor.
     *
     * @param array $values
     *            The parameters or objects
     */
    public function __construct(?Container $parent = null, ?Invoker $invoker = null)
    {
        // set parent and root
        $this->parent = $parent;
        if (!empty($this->parent)) {
            $this->root = $this->parent->getRoot();
        } else {
            $this->root = $this;
        }

        // Create an invoker
        if (!empty($invoker)) {
            $this->internalInvoker = $invoker;
        } else {
            if ($this->isRoot()) {
                $this->internalInvoker = new Invoker(new ResolverChain([
                    new ParameterNameContainerResolver($this),
                    new DefaultValueResolver()
                ]));
            }
        }

        // register the container as service
        $this->offsetSet('container', Container::value($this));
    }

    /**
     * Sets a factory to provide an object
     *
     * Objects must be defined as Closures.
     *
     * Allowing any PHP callable leads to difficult to debug problems
     * as function names (strings) are callable (creating a function with
     * the same name as an existing parameter would break your container).
     *
     * @param string $id
     *            The unique identifier for the parameter or object
     * @param mixed $factory
     *            The factory to define an object
     *            
     * @throws FrozenServiceException Prevent override of a frozen service
     */
    public function offsetSet($id, $factory)
    {
        if (isset($this->frozen[$id])) {
            throw new FrozenServiceException($id);
        }

        if (! is_callable($factory)) {
            // TODO: maso, 2021: set a message in class
            throw new ExpectedInvokableException();
        }

        $this->factories[$id] = $factory;
    }

    /**
     * Gets a parameter or an object.
     *
     * @param string $id
     *            The unique identifier for the parameter or object
     *            
     * @return mixed The value of the parameter or an object
     *        
     * @throws UnknownIdentifierException If the identifier is not defined
     */
    public function offsetGet($id)
    {
        // Check if factory exist
        if (! array_key_exists($id, $this->factories)) {
            // maso, 2020: fetch from parent
            if (!empty($this->parent)) {
                return $this->parent->get($id);
            }
            // XXX: maso,2021: root class finder

            // service not found
            throw new UnknownIdentifierException($id);
        }

        $factory = $this->factories[$id];
        $invoker = empty($this->internalInvoker) ? $this->root->internalInvoker :  $this->internalInvoker;
        $val = $invoker->call($factory);
        // value is used and you are not allowd to overrid
        $this->frozen[$id] = true;
        return $val;
    }

    /**
     * Checks if a parameter or an object is set.
     *
     * @param string $id
     *            The unique identifier for the parameter or object
     *            
     * @return bool
     */
    public function offsetExists($id)
    {
        $result = isset($this->factories[$id]);
        if (! $result && !empty($this->parent)) {
            return $this->parent->has($id);
        }
        return $result;
    }

    /**
     * Unsets a parameter or an object.
     *
     * @param string $id
     *            The unique identifier for the parameter or object
     */
    public function offsetUnset($id)
    {
        if (array_key_exists($id, $this->factories)) {
            unset($this->factories[$id], $this->frozen[$id]);
        }
    }

    public function set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    /**
     *
     * {@inheritdoc}
     * @see \Psr\Container\ContainerInterface::get()
     */
    public function get($id)
    {
        return $this->offsetGet($id);
    }

    /**
     *
     * {@inheritdoc}
     * @see \Psr\Container\ContainerInterface::has()
     */
    public function has($id)
    {
        return $this->offsetExists($id);
    }

    /**
     * Returns all defined value names.
     *
     * @return array An array of value names
     */
    public function keys()
    {
        return array_keys($this->factories);
    }

    /**
     * Calls the callabel
     *
     * @param callable $callable
     * @param array $parameters
     * @return mixed the result of callabel
     */
    public function call($callable, array $parameters = [])
    {
        return $this->internalInvoker->call($callable, $parameters);
    }

    /**
     * Injects to existed object
     *
     * @param mixed $object
     */
    public function injectOn($object)
    {
        // TODO:
    }

    /**
     * Gets raw data of the service
     *
     * @param mixed $id
     *            of hte object
     * @throws UnknownIdentifierException
     * @return Closure
     */
    public function raw($id): Closure
    {
        if (! $this->has($id)) {
            throw new UnknownIdentifierException($id);
        }
        return $this->factories[$id];
    }

    public function isRoot(): bool
    {
        return empty($this->parent);
    }

    public function getRoot(): Container
    {
        return $this->root;
    }

    public function addFactory(string $id, Closure $factory): Container
    {
        $this->offsetSet($id, self::factory($factory));
        return $this;
    }

    public function addService(string $id, Closure $factory): Container
    {
        $this->offsetSet($id, self::service($factory));
        return $this;
    }

    public function addValue(string $id, $value): Container
    {
        $this->offsetSet($id, self::value($value));
        return $this;
    }

    /**
     * Marks a callable as being a factory service.
     *
     * @param callable $callable
     *            A service definition to be used as a factory
     *            
     * @return callable The passed callable
     *        
     * @throws ExpectedInvokableException Service definition has to be a closure or an invokable object
     */
    public static function factory(Closure $callable): Closure
    {
        return $callable;
    }

    /**
     * Returns a closure that stores the result of the given service definition
     * for uniqueness in the scope of this instance of Pimple.
     *
     * @param callable $callable
     *            A service definition to wrap for uniqueness
     *            
     * @return Closure The wrapped closure
     */
    public static function service($callable): Closure
    {
        if (! is_callable($callable)) {
            throw new InvalidArgumentException('A factory must use to create a service.');
        }
        return function ($container) use ($callable) {
            static $service;

            if (null === $service) {
                // TODO: invoke the callable
                $invoker = new Invoker(new ParameterNameContainerResolver($container));
                $service = $invoker->call($callable);
            }

            return $service;
        };
    }

    /**
     * Protects a callable from being interpreted as a service.
     *
     * This is useful when you want to store a callable or value as a service. So the
     * value is not a factory.
     *
     * @param mixed $callable
     *            A callable to protect from being evaluated
     *            
     * @return Closure The protected closure
     */
    public static function value($value)
    {
        return function () use ($value) {
            return $value;
        };
    }
}
