<?php
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

    public function __construct(Container $container, array $ids)
    {
        $this->container = $container;
        $this->ids = $ids;
    }

    public function rewind()
    {
        reset($this->ids);
    }

    public function current()
    {
        return $this->container[current($this->ids)];
    }

    public function key()
    {
        return current($this->ids);
    }

    public function next()
    {
        next($this->ids);
    }

    public function valid()
    {
        return null !== key($this->ids);
    }
}
