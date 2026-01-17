<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Watchdog\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WatchdogNotFoundException extends NotFoundHttpException
{
    public function __construct(int $watchdogId)
    {
        parent::__construct(sprintf(
            'Watchdog with ID "%d" was not found.',
            $watchdogId,
        ));
    }
}
