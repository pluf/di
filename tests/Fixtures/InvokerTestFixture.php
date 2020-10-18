<?php
namespace Pluf\Tests\Fixtures;

/**
 * 
 * @author Mostafa Barmshory<mostafa.barmshory@gmail.com>
 *
 */
class InvokerTestFixture
{

    public $wasCalled = false;

    public function foo()
    {
        // Use this to make sure we are not called from a static context
        $this->wasCalled = true;
        return 'bar';
    }
}