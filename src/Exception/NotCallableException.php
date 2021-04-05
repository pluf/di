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
namespace Pluf\Di\Exception;

/**
 * The given callable is not actually callable.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class NotCallableException extends InvocationException
{
    /**
     * @param mixed $value
     */
    public static function fromInvalidCallable($value, bool $containerEntry = false): self
    {
        if (is_object($value)) {
            $message = sprintf('Instance of %s is not a callable', get_class($value));
        } elseif (is_array($value) && isset($value[0], $value[1])) {
            $class = is_object($value[0]) ? get_class($value[0]) : $value[0];
            $extra = method_exists($class, '__call') ? ' A __call() method exists but magic methods are not supported.' : '';
            $message = sprintf('%s::%s() is not a callable.%s', $class, $value[1], $extra);
        } elseif ($containerEntry) {
            $message = var_export($value, true) . ' is neither a callable nor a valid container entry';
        } else {
            $message = var_export($value, true) . ' is not a callable';
        }

        return new self($message);
    }
}
