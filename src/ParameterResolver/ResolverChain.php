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
namespace Pluf\Di\ParameterResolver;

use ReflectionFunctionAbstract;

/**
 * Dispatches the call to other resolvers until all parameters are resolved.
 *
 * Chain of responsibility pattern.
 *
 * @author Mostafa Barmshory<mostafa.barmshory@gmail.com>
 */
class ResolverChain implements ParameterResolver
{
    /**
     * @var ParameterResolver[]
     */
    private $resolvers;

    public function __construct(array $resolvers = array())
    {
        $this->resolvers = $resolvers;
    }

    public function getParameters(
        ReflectionFunctionAbstract $reflection,
        array $providedParameters,
        array $resolvedParameters
    ) {
        $reflectionParameters = $reflection->getParameters();

        foreach ($this->resolvers as $resolver) {
            $resolvedParameters = $resolver->getParameters(
                $reflection,
                $providedParameters,
                $resolvedParameters
            );

            $diff = array_diff_key($reflectionParameters, $resolvedParameters);
            if (empty($diff)) {
                // Stop traversing: all parameters are resolved
                return $resolvedParameters;
            }
        }

        return $resolvedParameters;
    }

    /**
     * Push a parameter resolver after the ones already registered.
     */
    public function appendResolver(ParameterResolver $resolver): void
    {
        $this->resolvers[] = $resolver;
    }

    /**
     * Insert a parameter resolver before the ones already registered.
     */
    public function prependResolver(ParameterResolver $resolver): void
    {
        array_unshift($this->resolvers, $resolver);
    }
}
