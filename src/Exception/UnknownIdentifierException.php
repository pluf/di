<?php
namespace Pluf\Di\Exception;

use Psr\Container\NotFoundExceptionInterface;

/**
 * The identifier of a valid service or parameter was expected.
 *
 * @author Mostafa Barmshory<mostafa.barmshory@gmail.com>
 */
class UnknownIdentifierException extends \InvalidArgumentException implements NotFoundExceptionInterface
{

    /**
     *
     * @param string $id
     *            The unknown identifier
     */
    public function __construct($id)
    {
        parent::__construct(\sprintf('Identifier "%s" is not defined.', $id));
    }
}
