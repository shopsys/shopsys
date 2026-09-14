<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Exception;

use Exception;

class OperatorNotSupportedException extends Exception
{
    public function __construct(string $operator, string $expressionBuilderClass)
    {
        parent::__construct(sprintf(
            'Operator "%s" is not expressed by the "%s" expression builder, extend the builder of the medium to express it.',
            $operator,
            $expressionBuilderClass,
        ));
    }
}
