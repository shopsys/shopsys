<?php

namespace Tests\FrameworkBundle\Unit\Component\Cron;

use DateTime;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig;
use Shopsys\FrameworkBundle\Component\Cron\CronFacade;
use Shopsys\FrameworkBundle\Component\Cron\CronModuleFacade;
use Shopsys\FrameworkBundle\Component\Cron\CronTimeResolver;
use Shopsys\Plugin\Cron\IteratedCronModuleInterface;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class CronFacadeTest extends TestCase
{
    public function testRunModuleByServiceId(): void
    {
        $serviceId = 'cronModuleServiceId';
        $cronModuleFacadeMock = $this->mockCronModuleFacade();
        $cronModuleServiceMock = $this->getMockForAbstractClass(SimpleCronModuleInterface::class);

        $cronModuleServiceMock->expects($this->once())->method('run');
        $this->expectMethodCallWithCronModuleConfig($cronModuleFacadeMock, 'unscheduleModule', $serviceId);

        $cronConfig = $this->createCronConfigWithRegisteredServices([
            $serviceId => $cronModuleServiceMock,
        ]);
        $this->createCronFacade($cronConfig, $cronModuleFacadeMock)->runModuleByServiceId($serviceId);
    }

    public function testRunIteratedModuleByServiceId(): void
    {
        $serviceId = 'cronModuleServiceId';
        $cronModuleFacadeMock = $this->mockCronModuleFacade();
        $cronModuleServiceMock = $this->getMockForAbstractClass(IteratedCronModuleInterface::class);

        $iterations = 3;
        $cronModuleServiceMock->expects($this->exactly($iterations))->method('iterate')->willReturnCallback(
            function () use (&$iterations) {
                $iterations--;
                return $iterations > 0;
            }
        );
        $this->expectMethodCallWithCronModuleConfig($cronModuleFacadeMock, 'unscheduleModule', $serviceId);

        $cronConfig = $this->createCronConfigWithRegisteredServices([
            $serviceId => $cronModuleServiceMock,
        ]);
        $this->createCronFacade($cronConfig, $cronModuleFacadeMock)->runModuleByServiceId($serviceId);
    }

    public function testScheduleModulesByTime(): void
    {
        $validServiceId = 'validCronModuleServiceId';
        $validCronModuleServiceMock = $this->getMockForAbstractClass(SimpleCronModuleInterface::class);
        $invalidServiceId = 'invalidCronModuleServiceId';
        $invalidCronModuleServiceMock = $this->getMockForAbstractClass(SimpleCronModuleInterface::class);
        $cronModuleFacadeMock = $this->mockCronModuleFacade();

        $cronTimeResolverMock = $this->createMock(CronTimeResolver::class);
        $cronTimeResolverMock->method('isValidAtTime')->willReturnCallback(
            function (CronModuleConfig $cronModuleConfig) use ($validServiceId) {
                return $cronModuleConfig->getServiceId() === $validServiceId;
            }
        );

        $cronModuleFacadeMock->expects($this->atLeastOnce())
            ->method('scheduleModules')
            ->with(Assert::callback(function ($modules) use ($validServiceId) {
                return count($modules) === 1 && current($modules)->getServiceId() === $validServiceId;
            }));

        $cronConfig = $this->createCronConfigWithRegisteredServices([
            $validServiceId => $validCronModuleServiceMock,
            $invalidServiceId => $invalidCronModuleServiceMock,
        ], $cronTimeResolverMock);
        $this->createCronFacade($cronConfig, $cronModuleFacadeMock)->scheduleModulesByTime(new DateTime());
    }

    public function testRunScheduledModules(): void
    {
        $scheduledServiceId = 'scheduledCronModuleServiceId';
        $scheduledCronModuleServiceMock = $this->getMockForAbstractClass(SimpleCronModuleInterface::class);
        $unscheduledServiceId = 'unscheduledCronModuleServiceId';
        $unscheduledCronModuleServiceMock = $this->getMockForAbstractClass(SimpleCronModuleInterface::class);
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
        $this->expectMethodCallWithCronModuleConfig($cronModuleFacadeMock, 'unscheduleModule', $scheduledServiceId);

        $this->createCronFacade($cronConfig, $cronModuleFacadeMock)->runScheduledModules();
    }

    private function createCronFacade(CronConfig $cronConfig, CronModuleFacade $cronModuleFacade): \Shopsys\FrameworkBundle\Component\Cron\CronFacade
    {
        $loggerMock = $this->createMock(Logger::class);
        /* @var $loggerMock \Symfony\Bridge\Monolog\Logger */

        return new CronFacade($loggerMock, $cronConfig, $cronModuleFacade);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Cron\CronModuleFacade|\PHPUnit\Framework\MockObject\MockObject
     */
    private function mockCronModuleFacade()
    {
        return $this->createMock(CronModuleFacade::class);
    }
    
    private function expectMethodCallWithCronModuleConfig(\PHPUnit\Framework\MockObject\MockObject $mock, string $methodName, string $serviceId): void
    {
        $mock->expects($this->atLeastOnce())
            ->method($methodName)
            ->with($this->attributeEqualTo('serviceId', $serviceId));
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Cron\CronTimeResolver|\PHPUnit\Framework\MockObject\MockObject|null $cronTimeResolverMock
     */
    private function createCronConfigWithRegisteredServices(array $servicesIndexedById, $cronTimeResolverMock = null): \Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig
    {
        $cronTimeResolver = $cronTimeResolverMock !== null ? $cronTimeResolverMock : new CronTimeResolver();
        $cronConfig = new CronConfig($cronTimeResolver);
        foreach ($servicesIndexedById as $serviceId => $service) {
            $cronConfig->registerCronModule($service, $serviceId, '*', '*');
        }

        return $cronConfig;
    }
}
