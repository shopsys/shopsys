<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron\Exception;

use Exception;

class InvalidCronModuleException extends Exception
{
    /**
     * @param string $serviceId
     */
    public function __construct($serviceId, ?Exception $previous = null)
    {
        parent::__construct('Module "' . $serviceId . '" does not have valid interface.', 0, $previous);
    }
}
