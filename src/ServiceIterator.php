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

use Iterator;

/**
 * Lazy service iterator.
 *
 * @author Mostafa Barmshory<mostafa.barmshory@gmail.com>
 */
final class ServiceIterator implements Iterator
{

    private $container;

    private $ids;

    /**
     * Creates new instance of iterator
     * 
     * @param Container $container
     * @param array $ids
     */
    public function __construct(Container $container, array $ids)
    {
        $this->container = $container;
        $this->ids = $ids;
    }

    /**
     * 
     * {@inheritDoc}
     * @see Iterator::rewind()
     */
    public function rewind()
    {
        reset($this->ids);
    }

    /**
     * 
     * {@inheritDoc}
     * @see Iterator::current()
     */
    public function current()
    {
        return $this->container[current($this->ids)];
    }

    /**
     * 
     * {@inheritDoc}
     * @see Iterator::key()
     */
    public function key()
    {
        return current($this->ids);
    }

    /**
     * 
     * {@inheritDoc}
     * @see Iterator::next()
     */
    public function next()
    {
        next($this->ids);
    }

    /**
     * 
     * {@inheritDoc}
     * @see Iterator::valid()
     */
    public function valid()
    {
        return null !== key($this->ids);
    }
}
