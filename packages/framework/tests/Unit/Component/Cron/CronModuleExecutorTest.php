<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Cron;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig;
use Shopsys\FrameworkBundle\Component\Cron\CronModuleExecutor;
use Shopsys\FrameworkBundle\Component\Cron\CronTimeResolver;
use Shopsys\Plugin\Cron\IteratedCronModuleInterface;

class CronModuleExecutorTest extends TestCase
{
    /**
     * @return true
     */
    public function testRunModuleSuspendAfterTimeout(): bool
    {
        $cronModuleServiceMock = $this->getMockForAbstractClass(IteratedCronModuleInterface::class);
        $cronModuleServiceMock->expects($this->once())->method('sleep');
        $cronModuleServiceMock->method('iterate')->willReturnCallback(function (): bool {
            usleep(1000);

            return true;
        });

        $cronModuleExecutor = $this->getCronModuleExecutor([
            get_class($cronModuleServiceMock) => $cronModuleServiceMock,
        ]);

        $this->assertSame(
            CronModuleExecutor::RUN_STATUS_SUSPENDED,
            $cronModuleExecutor->runModule($cronModuleServiceMock, false),
        );
    }

    public function testRunModuleAfterTimeout(): void
    {
        $cronModuleServiceMock = $this->getMockForAbstractClass(IteratedCronModuleInterface::class);
        $cronModuleServiceMock->expects($this->never())->method('iterate');

        $cronModuleExecutor = $this->getCronModuleExecutor([
            get_class($cronModuleServiceMock) => $cronModuleServiceMock,
        ]);

        sleep(1);
        $this->assertSame(
            CronModuleExecutor::RUN_STATUS_TIMEOUT,
            $cronModuleExecutor->runModule($cronModuleServiceMock, false),
        );
    }

    public function testRunModule(): void
    {
        $cronModuleServiceMock = $this->getMockForAbstractClass(IteratedCronModuleInterface::class);
        $cronModuleServiceMock->expects($this->never())->method('wakeUp');
        $cronModuleServiceMock->expects($this->once())->method('iterate')->willReturn(false);

        $cronModuleExecutor = $this->getCronModuleExecutor([
            get_class($cronModuleServiceMock) => $cronModuleServiceMock,
        ]);

        $this->assertSame(
            CronModuleExecutor::RUN_STATUS_OK,
            $cronModuleExecutor->runModule($cronModuleServiceMock, false),
        );
    }

    public function testRunSuspendedModule(): void
    {
        $cronModuleServiceMock = $this->getMockForAbstractClass(IteratedCronModuleInterface::class);
        $cronModuleServiceMock->expects($this->once())->method('wakeUp');
        $cronModuleServiceMock->method('iterate')->willReturn(false);

        $cronModuleExecutor = $this->getCronModuleExecutor([
            get_class($cronModuleServiceMock) => $cronModuleServiceMock,
        ]);

        $cronModuleExecutor->runModule($cronModuleServiceMock, true);
    }

    /**
     * @param mixed[] $servicesIndexedById
     * @return \Shopsys\FrameworkBundle\Component\Cron\CronModuleExecutor
     */
    private function getCronModuleExecutor(array $servicesIndexedById): CronModuleExecutor
    {
        $cronTimeResolver = new CronTimeResolver();
        $cronConfig = new CronConfig($cronTimeResolver);

        foreach ($servicesIndexedById as $serviceId => $service) {
            $cronConfig->registerCronModuleInstance(
                $service,
                $serviceId,
                '*',
                '*',
                CronModuleConfig::DEFAULT_INSTANCE_NAME,
                'testing cron',
                'every minute',
                CronModuleConfig::RUN_EVERY_MIN_DEFAULT,
                1,
            );
        }

        return new CronModuleExecutor($cronConfig);
    }
}
