<?php

namespace Shopsys\FrameworkBundle\Component\Cron\Exception;

use Exception;

class InvalidCronModuleException extends Exception implements CronException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $serviceId, Exception $previous = null)
    {
        parent::__construct('Module "' . $serviceId . '" does not have valid interface.', 0, $previous);
    }
}
