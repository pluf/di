<?php
namespace Pluf\Tests\Fixtures;

/**
 * 
 * @author Mostafa Barmshory<mostafa.barmshory@gmail.com>
 *
 */
class NonInvokable
{

    public function __call($a, $b)
    {}
}
