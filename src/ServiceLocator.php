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

use Pluf\Di\Exception\UnknownIdentifierException;
use Psr\Container\ContainerInterface;

/**
 * PSR-11 service locator.
 *
 * @author Mostafa Barmshory<mostafa.barmshory@gmail.com>
 */
class ServiceLocator implements ContainerInterface
{

    private $container;

    private $aliases = [];

    /**
     *
     * @param Container $container
     *            The Container instance used to locate services
     * @param array $ids
     *            Array of service ids that can be located. String keys can be used to define aliases
     */
    public function __construct(Container $container, array $ids)
    {
        $this->container = $container;

        foreach ($ids as $key => $id) {
            $this->aliases[\is_int($key) ? $id : $key] = $id;
        }
    }

    /**
     *
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (! isset($this->aliases[$id])) {
            throw new UnknownIdentifierException($id);
        }

        return $this->container[$this->aliases[$id]];
    }

    /**
     *
     * {@inheritdoc}
     */
    public function has($id)
    {
        return isset($this->aliases[$id]) && isset($this->container[$this->aliases[$id]]);
    }
}
