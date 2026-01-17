<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Module\Exception;

use Exception;

class UnsupportedModuleException extends Exception
{
    /**
     * @param string $moduleName
     */
    public function __construct($moduleName, ?Exception $previous = null)
    {
        parent::__construct(sprintf('Module "%s" is not supported', $moduleName), 0, $previous);
    }
}
