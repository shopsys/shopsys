<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Exception;

use Exception;

/**
 * An expression only ever makes sense in the medium that built it, so it is refused right away
 * instead of failing later as an unreadable error of that medium.
 */
class ExpressionFromForeignMediumException extends Exception
{
    public function __construct(string $expectedType, object $expression, string $consumerClass)
    {
        parent::__construct(sprintf(
            '"%s" expects an expression of type "%s", but got "%s". Build the expression with the builder of the same adapter.',
            $consumerClass,
            $expectedType,
            $expression::class,
        ));
    }
}
