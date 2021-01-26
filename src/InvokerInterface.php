<?php
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