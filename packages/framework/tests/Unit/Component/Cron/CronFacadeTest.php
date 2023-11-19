<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Cron;

use DateTime;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig;
use Shopsys\FrameworkBundle\Component\Cron\CronFacade;
use Shopsys\FrameworkBundle\Component\Cron\CronModuleExecutor;
use Shopsys\FrameworkBundle\Component\Cron\CronModuleFacade;
use Shopsys\FrameworkBundle\Component\Cron\CronTimeResolver;
use Shopsys\Plugin\Cron\IteratedCronModuleInterface;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class CronFacadeTest extends TestCase
{
    public function testRunModuleByServiceId(): void
    {
        $cronModuleFacadeMock = $this->mockCronModuleFacade();
        $cronModuleServiceMock = $this->getMockForAbstractClass(SimpleCronModuleInterface::class);

        $cronModuleServiceMock->expects($this->once())->method('run');

        $serviceId = get_class($cronModuleServiceMock);

        $cronConfig = $this->createCronConfigWithRegisteredServices([
            $serviceId => $cronModuleServiceMock,
        ]);
        $this->createCronFacade($cronConfig, $cronModuleFacadeMock)->runModuleByServiceId($serviceId);
    }

    /**
     * @return bool
     */
    public function testRunIteratedModuleByServiceId(): bool
    {
        $cronModuleFacadeMock = $this->mockCronModuleFacade();
        $cronModuleServiceMock = $this->getMockForAbstractClass(IteratedCronModuleInterface::class);

        $iterations = 3;
        $cronModuleServiceMock->expects($this->exactly($iterations))->method('iterate')->willReturnCallback(
            function () use (&$iterations) {
                $iterations--;

                return $iterations > 0;
            },
        );

        $serviceId = get_class($cronModuleServiceMock);

        $cronConfig = $this->createCronConfigWithRegisteredServices([
            $serviceId => $cronModuleServiceMock,
        ]);
        $this->createCronFacade($cronConfig, $cronModuleFacadeMock)->runModuleByServiceId($serviceId);
    }

    /**
     * @return bool
     */
    public function testScheduleModulesByTime(): bool
    {
        $validCronModuleServiceMock = $this->getMockForAbstractClass(SimpleCronModuleInterface::class);
        $validServiceId = get_class($validCronModuleServiceMock);
        $invalidCronModuleServiceMock = $this->getMockForAbstractClass(SimpleCronModuleInterface::class);
        $invalidServiceId = get_class($invalidCronModuleServiceMock);
        $cronModuleFacadeMock = $this->mockCronModuleFacade();

        $cronTimeResolverMock = $this->createMock(CronTimeResolver::class);
        $cronTimeResolverMock->method('isValidAtTime')->willReturnCallback(
            function (CronModuleConfig $cronModuleConfig) use ($validServiceId): bool {
                return $cronModuleConfig->getServiceId() === $validServiceId;
            },
        );

        $cronModuleFacadeMock->expects($this->atLeastOnce())
            ->method('scheduleModules')
            ->with(Assert::callback(function ($modules) use ($validServiceId): bool {
                return count($modules) === 1 && current($modules)->getServiceId() === $validServiceId;
            }));

        $cronConfig = $this->createCronConfigWithRegisteredServices([
            $validServiceId => $validCronModuleServiceMock,
            $invalidServiceId => $invalidCronModuleServiceMock,
        ], $cronTimeResolverMock);
        $this->createCronFacade($cronConfig, $cronModuleFacadeMock)->scheduleModulesByTime(new DateTime());
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig[]
     */
    public function testRunScheduledModules(): array
    {
        $scheduledCronModuleServiceMock = $this->getMockForAbstractClass(SimpleCronModuleInterface::class);
        $scheduledServiceId = get_class($scheduledCronModuleServiceMock);
        $unscheduledCronModuleServiceMock = $this->getMockForAbstractClass(SimpleCronModuleInterface::class);
        $unscheduledServiceId = get_class($unscheduledCronModuleServiceMock);
        $cronModuleFacadeMock = $this->mockCronModuleFacade();

        $scheduledCronModuleServiceMock->expects($this->once())->method('run');
        $unscheduledCronModuleServiceMock->expects($this->never())->method('run');

        $cronConfig = $this->createCronConfigWithRegisteredServices([
            $scheduledServiceId => $scheduledCronModuleServiceMock,
            $unscheduledServiceId => $unscheduledCronModuleServiceMock,
        ]);
        $cronModuleFacadeMock
            ->method('getOnlyScheduledCronModuleConfigs')
            ->willReturnCallback(function () use ($scheduledServiceId, $scheduledCronModuleServiceMock) {
                return [new CronModuleConfig($scheduledCronModuleServiceMock, $scheduledServiceId, '*', '*')];
            });

        $this->createCronFacade($cronConfig, $cronModuleFacadeMock)->runScheduledModulesForInstance(
            CronModuleConfig::DEFAULT_INSTANCE_NAME,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig $cronConfig
     * @param \Shopsys\FrameworkBundle\Component\Cron\CronModuleFacade $cronModuleFacade
     * @return \Shopsys\FrameworkBundle\Component\Cron\CronFacade
     */
    private function createCronFacade(CronConfig $cronConfig, CronModuleFacade $cronModuleFacade): \Shopsys\FrameworkBundle\Component\Cron\CronFacade
    {
        /** @var \Symfony\Bridge\Monolog\Logger $loggerMock */
        $loggerMock = $this->createMock(Logger::class);

        $cronModuleExecutor = new CronModuleExecutor($cronConfig);

        return new CronFacade($loggerMock, $cronConfig, $cronModuleFacade, $cronModuleExecutor);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Cron\CronModuleFacade|\PHPUnit\Framework\MockObject\MockObject
     */
    private function mockCronModuleFacade(): \PHPUnit\Framework\MockObject\MockObject|\Shopsys\FrameworkBundle\Component\Cron\CronModuleFacade
    {
        return $this->createMock(CronModuleFacade::class);
    }

    /**
     * @param mixed[] $servicesIndexedById
     * @param \Shopsys\FrameworkBundle\Component\Cron\CronTimeResolver|null $cronTimeResolverMock
     * @return \Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig
     */
    private function createCronConfigWithRegisteredServices(array $servicesIndexedById, $cronTimeResolverMock = null): \Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig
    {
        $cronTimeResolver = $cronTimeResolverMock !== null ? $cronTimeResolverMock : new CronTimeResolver();
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
                CronModuleConfig::TIMEOUT_ITERATED_CRON_SEC_DEFAULT,
            );
        }

        return $cronConfig;
    }
}
