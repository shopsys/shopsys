<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Exception;

use Exception;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;

/**
 * The operation would either fail in the medium or quietly match nothing on the path, so it is refused
 * the moment it is asked for.
 */
class OperatorNotApplicableToPathException extends Exception
{
    public function __construct(string $operator, PathDescription $pathDescription, string $reason)
    {
        parent::__construct(sprintf(
            'Operator "%s" cannot be applied to path "%s": %s',
            $operator,
            $pathDescription->path,
            $reason,
        ));
    }
}
