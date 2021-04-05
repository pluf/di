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
namespace Pluf\Di\Reflection;

use Pluf\Di\Exception\NotCallableException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;

/**
 * Create a reflection object from a callable.
 *
 * @author Mostafa Barmshory<mostafa.barmshory@gmail.com>
 */
class CallableReflection
{
    /**
     * @param callable $callable
     *
     * @throws NotCallableException|\ReflectionException
     *
     * TODO Use the `callable` type-hint once support for PHP 5.4 and up.
     */
    public static function create($callable): ReflectionFunctionAbstract
    {
        // Closure
        if ($callable instanceof \Closure) {
            return new ReflectionFunction($callable);
        }

        // Array callable
        if (is_array($callable)) {
            [$class, $method] = $callable;

            if (! method_exists($class, $method)) {
                throw NotCallableException::fromInvalidCallable($callable);
            }

            return new ReflectionMethod($class, $method);
        }

        // Callable object (i.e. implementing __invoke())
        if (is_object($callable) && method_exists($callable, '__invoke')) {
            return new ReflectionMethod($callable, '__invoke');
        }

        // Callable class (i.e. implementing __invoke())
        if (is_string($callable) && class_exists($callable) && method_exists($callable, '__invoke')) {
            return new ReflectionMethod($callable, '__invoke');
        }

        // Standard function
        if (is_string($callable) && function_exists($callable)) {
            return new ReflectionFunction($callable);
        }

        throw new NotCallableException(sprintf(
            '%s is not a callable',
            is_string($callable) ? $callable : 'Instance of ' . get_class($callable)
        ));
    }
}
