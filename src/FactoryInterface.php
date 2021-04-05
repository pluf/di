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

use Pluf\Di\Exception\DependencyException;
use Pluf\Di\Exception\DependencyNotFoundException;

/**
 * Describes the basic interface of a factory.
 *
 * @author Mostafa Barmshory<mostafa.barmshory@gmail.com>
 */
interface FactoryInterface
{

    /**
     * Resolves an entry by its name.
     * If given a class name, it will return a new instance of that class.
     *
     * @param string $name
     *            Entry name or a class name.
     * @param array $parameters
     *            Optional parameters to use to build the entry. Use this to force specific
     *            parameters to specific values. Parameters not defined in this array will
     *            be automatically resolved.
     *            
     * @throws \InvalidArgumentException The name parameter must be of type string.
     * @throws DependencyException Error while resolving the entry.
     * @throws DependencyNotFoundException No entry or class found for the given name.
     * @return mixed
     */
    public function make($name, array $parameters = []);
}