<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron\Config\Exception;

use Exception;
use Throwable;

class SentryMonitoringNotEnabledException extends Exception
{
    public function __construct(string $serviceId, ?Throwable $previous = null)
    {
        parent::__construct(
            'Cron module "' . $serviceId . '" sets Sentry monitoring options but "sentryMonitoring" is not enabled. Enable it or remove the option(s).',
            0,
            $previous,
        );
    }
}
