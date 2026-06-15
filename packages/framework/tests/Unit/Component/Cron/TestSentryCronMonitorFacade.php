<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Cron;

use Override;
use Sentry\CheckInStatus;
use Sentry\MonitorConfig;
use Shopsys\FrameworkBundle\Component\Cron\SentryCronMonitorFacade;
use Throwable;

class TestSentryCronMonitorFacade extends SentryCronMonitorFacade
{
    /**
     * @var array<int, array{status: string, slug: string, checkInId: string|null, hasMonitorConfig: bool}>
     */
    public array $capturedCheckIns = [];

    public ?Throwable $throwOnCapture = null;

    protected int $checkInIdSequence = 0;

    #[Override]
    protected function captureCheckIn(
        string $slug,
        CheckInStatus $status,
        ?MonitorConfig $monitorConfig = null,
        ?string $checkInId = null,
    ): ?string {
        if ($this->throwOnCapture !== null) {
            throw $this->throwOnCapture;
        }

        if ($status === CheckInStatus::inProgress()) {
            $checkInId = 'check-in-' . ++$this->checkInIdSequence;
        }

        $this->capturedCheckIns[] = [
            'status' => (string)$status,
            'slug' => $slug,
            'checkInId' => $checkInId,
            'hasMonitorConfig' => $monitorConfig !== null,
        ];

        return $checkInId;
    }
}
