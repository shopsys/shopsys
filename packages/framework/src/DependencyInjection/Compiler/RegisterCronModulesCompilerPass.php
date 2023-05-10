<?php

namespace Shopsys\FrameworkBundle\DependencyInjection\Compiler;

use Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RegisterCronModulesCompilerPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $cronConfigDefinition = $container->findDefinition(CronConfig::class);

        $cronInstances = $container->getParameter('cron_instances');

        $taggedServiceIds = $container->findTaggedServiceIds('shopsys.cron');

        foreach ($taggedServiceIds as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $instanceName = $tag['instanceName'] ?? CronModuleConfig::DEFAULT_INSTANCE_NAME;
                $instanceConfig = $this->getInstanceConfig($cronInstances, $instanceName);

                $cronConfigDefinition->addMethodCall(
                    'registerCronModuleInstance',
                    [
                        new Reference($serviceId),
                        $serviceId,
                        $tag['hours'],
                        $tag['minutes'],
                        $instanceName,
                        $tag['readableName'] ?? null,
                        $instanceConfig['run_every_min'],
                        $instanceConfig['timeout_iterated_cron_sec'],
                    ]
                );
            }
        }
    }

    /**
     * @param array<string, array{run_every_min: int|null, timeout_iterated_cron_sec: int|null}> $cronInstances
     * @param string $instanceName
     * @return array{run_every_min: int, timeout_iterated_cron_sec: int}
     */
    private function getInstanceConfig(array $cronInstances, string $instanceName): array
    {
        if (array_key_exists($instanceName, $cronInstances)) {
            $runEveryMin = $cronInstances[$instanceName]['run_every_min'] ?? CronModuleConfig::RUN_EVERY_MIN_DEFAULT;
            $timeoutIteratedCronSec = $cronInstances[$instanceName]['timeout_iterated_cron_sec'] ?? CronModuleConfig::TIMEOUT_ITERATED_CRON_SEC_DEFAULT;

            if ($runEveryMin < 0 || $runEveryMin > 30) {
                $runEveryMin = CronModuleConfig::RUN_EVERY_MIN_DEFAULT;
            }

            if ($timeoutIteratedCronSec < 0 || $timeoutIteratedCronSec > $runEveryMin * 60) {
                $timeoutIteratedCronSec = round($runEveryMin * 60 / 5);
            }

            return [
                'run_every_min' => $runEveryMin,
                'timeout_iterated_cron_sec' => $timeoutIteratedCronSec,
            ];
        }

        return [
            'run_every_min' => CronModuleConfig::RUN_EVERY_MIN_DEFAULT,
            'timeout_iterated_cron_sec' => CronModuleConfig::TIMEOUT_ITERATED_CRON_SEC_DEFAULT,
        ];
    }
}
