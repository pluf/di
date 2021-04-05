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

use Pluf\Di\Exception\InvocationException;
use Pluf\Di\Exception\NotCallableException;
use Pluf\Di\Exception\NotEnoughParametersException;

/**
 * Invoke a callable.
 * 
 * @author Mostafa Barmshory<mostafa.barmshory@gmail.com>
 */
interface InvokerInterface
{

    /**
     * Call the given function using the given parameters.
     *
     * @param callable|array|string $callable
     *            Function to call.
     * @param array $parameters
     *            Parameters to use.
     * @return mixed Result of the function.
     * @throws InvocationException Base exception class for all the sub-exceptions below.
     * @throws NotCallableException
     * @throws NotEnoughParametersException
     */
    public function call($callable, array $parameters = []);
}