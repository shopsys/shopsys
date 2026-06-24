<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Cron;

use Monolog\Logger;
use Override;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig;
use Shopsys\FrameworkBundle\Component\Cron\CronModuleExecutor;
use Shopsys\FrameworkBundle\Component\Cron\CronModuleFacade;
use Shopsys\FrameworkBundle\Component\Cron\CronModuleRunnerFacade;
use Shopsys\FrameworkBundle\Component\Cron\CronTimeResolver;
use Shopsys\FrameworkBundle\Component\Cron\SentryCronMonitorFacade;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class CronModuleRunnerFacadeTest extends TestCase
{
    private string $serviceId;

    private CronConfig $cronConfig;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $cronModuleService = $this->createStub(SimpleCronModuleInterface::class);
        $this->serviceId = get_class($cronModuleService);

        $this->cronConfig = new CronConfig(new CronTimeResolver(), new TransformStringHelper());
        $this->cronConfig->registerCronModuleInstance(
            $cronModuleService,
            $this->serviceId,
            '* * * * *',
            CronModuleConfig::DEFAULT_INSTANCE_NAME,
        );
    }

    public function testRunDisabledModuleReturnsOkUnschedulesItAndReportsHealthyRun(): void
    {
        $cronModuleFacadeMock = $this->createMock(CronModuleFacade::class);
        $cronModuleFacadeMock->method('isModuleDisabled')->willReturn(true);
        $cronModuleFacadeMock->expects($this->once())->method('unscheduleModule');
        $cronModuleFacadeMock->expects($this->never())->method('markCronAsStarted');

        $sentryCronMonitorFacadeMock = $this->createMock(SentryCronMonitorFacade::class);
        $sentryCronMonitorFacadeMock->expects($this->once())->method('reportDisabledRunAsHealthy');

        $result = $this->createCronModuleRunnerFacade($cronModuleFacadeMock, sentryCronMonitorFacade: $sentryCronMonitorFacadeMock)
            ->runModuleByServiceIdInContext($this->serviceId);

        $this->assertSame(CronModuleExecutor::RUN_STATUS_OK, $result);
    }

    public function testRunModuleSuccessfullyReturnsOkAndUnschedulesIt(): void
    {
        $cronModuleFacadeMock = $this->createMock(CronModuleFacade::class);
        $cronModuleFacadeMock->method('isModuleDisabled')->willReturn(false);
        $cronModuleFacadeMock->method('isModuleSuspended')->willReturn(false);
        $cronModuleFacadeMock->expects($this->once())->method('markCronAsStarted');
        $cronModuleFacadeMock->expects($this->once())->method('markCronAsEnded');
        $cronModuleFacadeMock->expects($this->once())->method('unscheduleModule');

        $cronModuleExecutorStub = $this->createStub(CronModuleExecutor::class);
        $cronModuleExecutorStub->method('runModule')->willReturn(CronModuleExecutor::RUN_STATUS_OK);

        $result = $this->createCronModuleRunnerFacade($cronModuleFacadeMock, $cronModuleExecutorStub)
            ->runModuleByServiceIdInContext($this->serviceId);

        $this->assertSame(CronModuleExecutor::RUN_STATUS_OK, $result);
    }

    public function testRunModuleReturnsErrorAndMarksFailedOnException(): void
    {
        $cronModuleFacadeMock = $this->createMock(CronModuleFacade::class);
        $cronModuleFacadeMock->method('isModuleDisabled')->willReturn(false);
        $cronModuleFacadeMock->method('isModuleSuspended')->willReturn(false);
        $cronModuleFacadeMock->expects($this->once())->method('markCronAsFailed');
        $cronModuleFacadeMock->expects($this->never())->method('markCronAsEnded');
        $cronModuleFacadeMock->expects($this->never())->method('unscheduleModule');

        $cronModuleExecutorStub = $this->createStub(CronModuleExecutor::class);
        $cronModuleExecutorStub->method('runModule')->willThrowException(new RuntimeException('Cron failed'));

        $result = $this->createCronModuleRunnerFacade($cronModuleFacadeMock, $cronModuleExecutorStub)
            ->runModuleByServiceIdInContext($this->serviceId);

        $this->assertSame(CronModuleExecutor::RUN_STATUS_ERROR, $result);
    }

    public function testRunSuspendedModuleSuspendsItAgainWhenExecutorReturnsSuspended(): void
    {
        $cronModuleFacadeMock = $this->createMock(CronModuleFacade::class);
        $cronModuleFacadeMock->method('isModuleDisabled')->willReturn(false);
        $cronModuleFacadeMock->method('isModuleSuspended')->willReturn(true);
        $cronModuleFacadeMock->expects($this->once())->method('markCronAsEnded');
        $cronModuleFacadeMock->expects($this->once())->method('suspendModule');
        $cronModuleFacadeMock->expects($this->never())->method('unscheduleModule');

        $cronModuleExecutorStub = $this->createStub(CronModuleExecutor::class);
        $cronModuleExecutorStub->method('runModule')->willReturn(CronModuleExecutor::RUN_STATUS_SUSPENDED);

        $result = $this->createCronModuleRunnerFacade($cronModuleFacadeMock, $cronModuleExecutorStub)
            ->runModuleByServiceIdInContext($this->serviceId);

        $this->assertSame(CronModuleExecutor::RUN_STATUS_SUSPENDED, $result);
    }

    private function createCronModuleRunnerFacade(
        CronModuleFacade $cronModuleFacade,
        ?CronModuleExecutor $cronModuleExecutor = null,
        ?SentryCronMonitorFacade $sentryCronMonitorFacade = null,
    ): CronModuleRunnerFacade {
        return new CronModuleRunnerFacade(
            $this->createStub(Logger::class),
            $this->cronConfig,
            $cronModuleFacade,
            $cronModuleExecutor ?? $this->createStub(CronModuleExecutor::class),
            $sentryCronMonitorFacade ?? $this->createStub(SentryCronMonitorFacade::class),
        );
    }
}
