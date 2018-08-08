<?php

namespace Shopsys\FrameworkBundle\Model\Module\Exception;

use Exception;

class UnsupportedModuleException extends Exception implements ModuleException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $moduleName, Exception $previous = null)
    {
        parent::__construct(sprintf('Module "%s" is not supported', $moduleName), 0, $previous);
    }
}
