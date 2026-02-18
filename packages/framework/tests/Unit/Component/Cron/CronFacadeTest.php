<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Cron;

use Monolog\Logger;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig;
use Shopsys\FrameworkBundle\Component\Cron\CronFacade;
use Shopsys\FrameworkBundle\Component\Cron\CronModuleFacade;
use Shopsys\FrameworkBundle\Component\Cron\CronModuleProcessRunner;
use Shopsys\FrameworkBundle\Component\Cron\CronTimeResolver;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CronFacadeTest extends TestCase
{
    public function testScheduleModulesByTime(): void
    {
        $validCronModuleServiceStub = $this->createStub(SimpleCronModuleInterface::class);
        $validServiceId = get_class($validCronModuleServiceStub);
        $invalidCronModuleServiceStub = $this->createStub(SimpleCronModuleInterface::class);
        $invalidServiceId = get_class($invalidCronModuleServiceStub);

        $cronTimeResolverStub = $this->createStub(CronTimeResolver::class);
        $cronTimeResolverStub->method('isValidAtTime')->willReturnCallback(
            function (CronModuleConfig $cronModuleConfig) use ($validServiceId) {
                return $cronModuleConfig->getServiceId() === $validServiceId;
            },
        );

        $cronModuleFacadeMock = $this->createMock(CronModuleFacade::class);
        $cronModuleFacadeMock->expects($this->atLeastOnce())
            ->method('scheduleModules')
            ->with(Assert::callback(function ($modules) use ($validServiceId) {
                return count($modules) === 1 && current($modules)->getServiceId() === $validServiceId;
            }));

        $cronConfig = $this->createCronConfigWithRegisteredServices([
            $validServiceId => $validCronModuleServiceStub,
            $invalidServiceId => $invalidCronModuleServiceStub,
        ], $cronTimeResolverStub);
        $this->createCronFacade($cronConfig, $cronModuleFacadeMock)->scheduleModulesByTime(new DatePoint());
    }

    public function testRunSingleModuleDelegatesToProcessRunner(): void
    {
        $cronModuleFacadeStub = $this->createStub(CronModuleFacade::class);
        $cronModuleProcessRunnerMock = $this->createMock(CronModuleProcessRunner::class);

        $cronModuleProcessRunnerMock->expects($this->once())
            ->method('runModule')
            ->with('service.id', 'default', $this->isCallable(), true, null)
            ->willReturn(CronModuleProcessRunner::RESULT_SUCCESS);

        $cronConfig = $this->createCronConfigWithRegisteredServices([]);

        $result = $this->createCronFacade($cronConfig, $cronModuleFacadeStub, cronModuleProcessRunner: $cronModuleProcessRunnerMock)
            ->runSingleModule('service.id', 'default', static function (): void {}, true);

        $this->assertSame(CronModuleProcessRunner::RESULT_SUCCESS, $result);
    }

    public function testGetInstanceNamesReturnsUniqueNames(): void
    {
        $cronModuleFacadeStub = $this->createStub(CronModuleFacade::class);
        $cronModuleServiceStub = $this->createStub(SimpleCronModuleInterface::class);
        $serviceId = get_class($cronModuleServiceStub);

        $cronConfig = $this->createCronConfigWithRegisteredServices([
            $serviceId => $cronModuleServiceStub,
        ]);

        $instanceNames = $this->createCronFacade($cronConfig, $cronModuleFacadeStub)->getInstanceNames();

        $this->assertSame(['default'], $instanceNames);
    }

    private function createCronFacade(
        CronConfig $cronConfig,
        CronModuleFacade $cronModuleFacade,
        ?ParameterBagInterface $parameterBag = null,
        ?CronModuleProcessRunner $cronModuleProcessRunner = null,
    ): CronFacade {
        $loggerMock = $this->createStub(Logger::class);

        return new CronFacade(
            $loggerMock,
            $cronConfig,
            $cronModuleFacade,
            $parameterBag ?? $this->createStub(ParameterBagInterface::class),
            $cronModuleProcessRunner ?? $this->createStub(CronModuleProcessRunner::class),
        );
    }

    private function createCronConfigWithRegisteredServices(
        array $servicesIndexedById,
        ?CronTimeResolver $cronTimeResolverMock = null,
    ): CronConfig {
        $cronTimeResolver = $cronTimeResolverMock ?? new CronTimeResolver();
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
