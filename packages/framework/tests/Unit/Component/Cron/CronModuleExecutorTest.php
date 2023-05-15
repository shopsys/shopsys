<?php

namespace Tests\FrameworkBundle\Unit\Component\Cron;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig;
use Shopsys\FrameworkBundle\Component\Cron\CronModuleExecutor;
use Shopsys\FrameworkBundle\Component\Cron\CronTimeResolver;
use Shopsys\Plugin\Cron\IteratedCronModuleInterface;

class CronModuleExecutorTest extends TestCase
{
    public function testRunModuleSuspendAfterTimeout()
    {
        $cronModuleServiceMock = $this->getMockForAbstractClass(IteratedCronModuleInterface::class);
        $cronModuleServiceMock->expects($this->once())->method('sleep');
        $cronModuleServiceMock->method('iterate')->willReturnCallback(function () {
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

    public function testRunModuleAfterTimeout()
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

    public function testRunModule()
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

    public function testRunSuspendedModule()
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
     * @param array $servicesIndexedById
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
                CronModuleConfig::RUN_EVERY_MIN_DEFAULT,
                1,
            );
        }

        return new CronModuleExecutor(1, $cronConfig);
    }
}
