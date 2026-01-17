<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron\Config\Exception;

use Exception;

class CronModuleConfigNotFoundException extends Exception
{
    /**
     * @param string $serviceId
     */
    public function __construct($serviceId, ?Exception $previous = null)
    {
        parent::__construct('Cron module config with service ID "' . $serviceId . '" not found.', 0, $previous);
    }
}
