<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron;

use DateTimeInterface;
use Monolog\Logger;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CronFacade
{
    public function __construct(
        protected readonly Logger $logger,
        protected readonly CronConfig $cronConfig,
        protected readonly CronModuleFacade $cronModuleFacade,
        protected readonly ParameterBagInterface $parameterBag,
        protected readonly CronModuleProcessRunner $cronModuleProcessRunner,
    ) {
    }

    public function scheduleModulesByTime(DateTimeInterface $roundedTime): void
    {
        $cronModuleConfigsToSchedule = $this->cronConfig->getCronModuleConfigsByTime($roundedTime);
        $this->cronModuleFacade->scheduleModules($cronModuleConfigsToSchedule);
    }

    public function runScheduledModulesForInstance(
        string $instanceName,
        callable $processOutputCallback,
        bool $isOutputDecorated,
    ): void {
        $cronModuleConfigs = $this->cronConfig->getCronModuleConfigsForInstance($instanceName);

        $scheduledCronModuleConfigs = $this->cronModuleFacade->getOnlyScheduledCronModuleConfigs($cronModuleConfigs);

        $this->runModules(
            $scheduledCronModuleConfigs,
            $instanceName,
            $processOutputCallback,
            $isOutputDecorated,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig[] $cronModuleConfigs
     */
    protected function runModules(
        array $cronModuleConfigs,
        string $instanceName,
        callable $processOutputCallback,
        bool $isOutputDecorated,
    ): void {
        $unique = uniqid(more_entropy: true);

        $this->logger->pushProcessor(function ($record) use ($instanceName, $unique) {
            $record->extra['instance'] = $instanceName;
            $record->extra['runId'] = $unique;

            return $record;
        });

        $this->logger->info('Start of cron instance');

        try {
            $stopOnFailure = $this->shouldInstanceStopOnFailure($instanceName);

            foreach ($cronModuleConfigs as $cronModuleConfig) {
                if ($this->cronModuleFacade->isModuleDisabled($cronModuleConfig)) {
                    $this->cronModuleFacade->unscheduleModule($cronModuleConfig);

                    continue;
                }

                $result = $this->runSingleModule(
                    $cronModuleConfig->getServiceId(),
                    $instanceName,
                    $processOutputCallback,
                    $isOutputDecorated,
                    $unique,
                );

                if ($result !== CronModuleProcessRunner::RESULT_SUCCESS && $stopOnFailure) {
                    $this->logger->error('Stopping cron instance execution due to failure of a module');

                    break;
                }
            }
        } finally {
            $this->logger->info('End of cron instance');
            $this->logger->popProcessor();
        }
    }

    public function runSingleModule(
        string $serviceId,
        string $instanceName,
        callable $processOutputCallback,
        bool $isOutputDecorated,
        ?string $runId = null,
    ): string {
        return $this->cronModuleProcessRunner->runModule(
            $serviceId,
            $instanceName,
            $processOutputCallback,
            $isOutputDecorated,
            $runId,
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig[]
     */
    public function getAll(): array
    {
        return $this->cronConfig->getAllCronModuleConfigs();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig[]
     */
    public function getAllForInstance(string $instanceName): array
    {
        return $this->cronConfig->getCronModuleConfigsForInstance($instanceName);
    }

    /**
     * @return string[]
     */
    public function getInstanceNames(): array
    {
        return array_unique(
            array_map(
                static fn (CronModuleConfig $config) => $config->getInstanceName(),
                $this->getAll(),
            ),
        );
    }

    public function shouldInstanceStopOnFailure(string $instanceName): bool
    {
        $cronInstances = $this->parameterBag->get('cron_instances');

        return $cronInstances[$instanceName]['stop_on_failure'] ?? true;
    }
}
