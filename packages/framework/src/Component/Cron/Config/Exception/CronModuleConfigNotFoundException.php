<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron\Config\Exception;

use Exception;

class CronModuleConfigNotFoundException extends Exception
{
    public function __construct(string $serviceId, ?Exception $previous = null)
    {
        parent::__construct('Cron module config with service ID "' . $serviceId . '" not found.', 0, $previous);
    }
}
