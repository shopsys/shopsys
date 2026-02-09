<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Module\Exception;

use Exception;

class UnsupportedModuleException extends Exception
{
    public function __construct(string $moduleName, ?Exception $previous = null)
    {
        parent::__construct(sprintf('Module "%s" is not supported', $moduleName), 0, $previous);
    }
}
