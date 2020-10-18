<?php
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
class Container implements ArrayAccess, ContainerInterface
{

    private array $factories = [];

    private array $frozen = [];

    /**
     * Stores list of all keys in this container
     *
     * @var array
     */
    private array $keys = [];

    /**
     * Parent container is used to resolve services hericically.
     *
     * @var Container
     */
    private ?Container $parent = null;

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
    public function __construct(?Container $parent = null)
    {
        $this->parent = $parent;
        // Create an invoker
        $this->internalInvoker = new Invoker(new ResolverChain([
            new ParameterNameContainerResolver($this),
            new DefaultValueResolver()
        ]));

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

        // TODO: maso, 2020: check the $value
        if (! is_callable($factory)) {
            throw new ExpectedInvokableException('Factory is not a Closure or invokable object.');
        }

        $this->factories[$id] = $factory;
        $this->keys[$id] = true;
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
        if (! isset($this->keys[$id])) {
            // TODO: maso, 2020: fetch from parent
            throw new UnknownIdentifierException($id);
        }

        $factory = $this->factories[$id];
        $val = $this->internalInvoker->call($factory);
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
        return isset($this->keys[$id]);
    }

    /**
     * Unsets a parameter or an object.
     *
     * @param string $id
     *            The unique identifier for the parameter or object
     */
    public function offsetUnset($id)
    {
        if (isset($this->keys[$id])) {
            unset($this->factories[$id], $this->keys[$id], $this->frozen);
        }
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

    public function raw($id): Closure
    {
        if (! $this->has($id)) {
            throw new UnknownIdentifierException($id);
        }
        return $this->factories[$id];
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
