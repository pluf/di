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
 * Resolves the parameters to use to call the callable.
 *
 * @author Mostafa Barmshory<mostafa.barmshory@gmail.com>
 */
interface ParameterResolver
{
    /**
     * Resolves the parameters to use to call the callable.
     *
     * `$resolvedParameters` contains parameters that have already been resolved.
     *
     * Each ParameterResolver must resolve parameters that are not already
     * in `$resolvedParameters`. That allows to chain multiple ParameterResolver.
     *
     * @param ReflectionFunctionAbstract $reflection Reflection object for the callable.
     * @param array $providedParameters Parameters provided by the caller.
     * @param array $resolvedParameters Parameters resolved (indexed by parameter position).
     *
     * @return array
     */
    public function getParameters(
        ReflectionFunctionAbstract $reflection,
        array $providedParameters,
        array $resolvedParameters
    );
}
