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

use Pluf\Di\Exception\NotCallableException;
use Pluf\Di\Exception\NotEnoughParametersException;
use Pluf\Di\ParameterResolver\AssociativeArrayResolver;
use Pluf\Di\ParameterResolver\DefaultValueResolver;
use Pluf\Di\ParameterResolver\NumericArrayResolver;
use Pluf\Di\ParameterResolver\ParameterResolver;
use Pluf\Di\ParameterResolver\ResolverChain;
use Pluf\Di\Reflection\CallableReflection;
use Psr\Container\ContainerInterface;

/**
 * Invoke a callable.
 *
 * Parameters may resolve with deffirent methods such as Container resolver.
 *
 * @author Mostafa Barmshory<mostafa.barmshory@gmail.com>
 */
class Invoker implements InvokerInterface
{

    /**
     *
     * @var CallableResolver|null
     */
    private ?CallableResolver $callableResolver = null;

    /**
     *
     * @var ParameterResolver
     */
    private ParameterResolver $parameterResolver;

    /**
     *
     * @var ContainerInterface|null
     */
    private $container;

    public function __construct(?ParameterResolver $parameterResolver = null, ?ContainerInterface $container = null)
    {
        $this->parameterResolver = $parameterResolver ?: $this->createParameterResolver();

        $this->container = $container;
        if ($container) {
            $this->callableResolver = new CallableResolver($container);
        }
    }

    /**
     * Calls a the callable
     *
     * @param callable $callable
     *            to call
     * @param array $parameters
     *            parameter to resolve
     * @throws NotCallableException
     * @throws NotEnoughParametersException
     * @return mixed the result of callable
     */
    public function call($callable, array $parameters = [])
    {
        if ($this->callableResolver) {
            $callable = $this->callableResolver->resolve($callable);
        }

        if (! is_callable($callable)) {
            throw new NotCallableException(sprintf('%s is not a callable', is_object($callable) ? 'Instance of ' . get_class($callable) : var_export($callable, true)));
        }

        $callableReflection = CallableReflection::create($callable);

        $args = $this->parameterResolver->getParameters($callableReflection, $parameters, array());

        // Sort by array key because call_user_func_array ignores numeric keys
        ksort($args);

        // Check all parameters are resolved
        $diff = array_diff_key($callableReflection->getParameters(), $args);
        if (! empty($diff)) {
            /** @var \ReflectionParameter $parameter */
            $parameter = reset($diff);
            throw new NotEnoughParametersException(sprintf('Unable to invoke the callable because no value was given for parameter %d ($%s)', $parameter->getPosition() + 1, $parameter->name));
        }

        return call_user_func_array($callable, $args);
    }

    /**
     * Create the default parameter resolver.
     */
    private function createParameterResolver(): ParameterResolver
    {
        return new ResolverChain(array(
            new NumericArrayResolver(),
            new AssociativeArrayResolver(),
            new DefaultValueResolver()
        ));
    }

    /**
     *
     * @return ParameterResolver By default it's a ResolverChain
     */
    public function getParameterResolver(): ParameterResolver
    {
        return $this->parameterResolver;
    }

    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }

    /**
     *
     * @return CallableResolver|null Returns null if no container was given in the constructor.
     */
    public function getCallableResolver(): ?CallableResolver
    {
        return $this->callableResolver;
    }
}