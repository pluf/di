<?php
namespace Pluf\Tests\Fixtures;

use Exception;

/**
 * 
 * @author Mostafa Barmshory<mostafa.barmshory@gmail.com>
 *
 */
class InvokerTestMagicMethodFixture
{

    public $wasCalled = false;

    public function __call($name, $args)
    {
        if ($name === 'foo') {
            $this->wasCalled = true;
            return 'bar';
        }
        throw new Exception('Unknown method');
    }
}