<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Exception;

use Exception;

class OperatorNotSupportedException extends Exception
{
    public function __construct(string $operator, string $expressionBuilderClass)
    {
        parent::__construct(sprintf(
            'Operator "%s" is not supported by the "%s" expression builder. Ask the capabilities of the adapter through "supportsOperator()" before building an expression with it.',
            $operator,
            $expressionBuilderClass,
        ));
    }
}
