<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Exception;

use Exception;

class ToManySelectNotSupportedException extends Exception
{
    public function __construct(string $path)
    {
        parent::__construct(sprintf(
            'Path "%s" leads through a to-many association and cannot be selected, because joining it would list an entity once per related row. Use a virtual field with a transform instead.',
            $path,
        ));
    }
}
