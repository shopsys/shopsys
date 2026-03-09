<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Cron;

use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Cron\CronModuleFactory;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class CronModuleTest extends TestCase
{
    private CronModuleFactory $cronModuleFactory;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->cronModuleFactory = new CronModuleFactory(new EntityNameResolver([]));
    }

    public function testSuspendSetsScheduledAndSuspendedFlags(): void
    {
        $cronModule = $this->cronModuleFactory->create('service.id');

        $cronModule->suspend();

        $this->assertTrue($cronModule->isSuspended());
        $this->assertTrue($cronModule->isScheduled());
    }

    public function testUnscheduleResetsScheduledAndSuspendedFlags(): void
    {
        $cronModule = $this->cronModuleFactory->create('service.id');
        $cronModule->suspend();

        $cronModule->unschedule();

        $this->assertFalse($cronModule->isSuspended());
        $this->assertFalse($cronModule->isScheduled());
    }
}
