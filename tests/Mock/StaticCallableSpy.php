<?php
namespace Pluf\Tests\Mock;

/**
 * Mock a callable and spies being called.
 *
 * @author Mostafa Barmshory<mostafa.barmshory@gmail.com>
 */
class StaticCallableSpy
{

    /**
     *
     * @var int
     */
    public static int $callCount = 0;

    public function __invoke()
    {
        StaticCallableSpy::$callCount ++;
    }
}