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
 * Tries to map an associative array (string-indexed) to the parameter names.
 *
 * E.g. `->call($callable, ['foo' => 'bar'])` will inject the string `'bar'`
 * in the parameter named `$foo`.
 *
 * Parameters that are not indexed by a string are ignored.
 *
 * @author Mostafa Barmshory<mostafa.barmshory@gmail.com>
 */
class AssociativeArrayResolver implements ParameterResolver
{
    public function getParameters(
        ReflectionFunctionAbstract $reflection,
        array $providedParameters,
        array $resolvedParameters
    ) {
        $parameters = $reflection->getParameters();

        // Skip parameters already resolved
        if (! empty($resolvedParameters)) {
            $parameters = array_diff_key($parameters, $resolvedParameters);
        }

        foreach ($parameters as $index => $parameter) {
            if (array_key_exists($parameter->name, $providedParameters)) {
                $resolvedParameters[$index] = $providedParameters[$parameter->name];
            }
        }

        return $resolvedParameters;
    }
}
