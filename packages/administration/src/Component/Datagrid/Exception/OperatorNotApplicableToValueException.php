<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Exception;

use Exception;

/**
 * A medium without a schema learns the type of a value only when it meets it — and a value the operation
 * makes no sense on is refused the same way a schema-aware medium refuses the path up front.
 */
class OperatorNotApplicableToValueException extends Exception
{
    public function __construct(string $operator, mixed $value)
    {
        parent::__construct(sprintf(
            'Operator "%s" cannot be applied to a value of type "%s".',
            $operator,
            get_debug_type($value),
        ));
    }
}
