<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\DependencyInjection\Compiler;

use Shopsys\FrameworkBundle\Component\ServiceAliasContainerInfoRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SaveServiceAliasContainerInfoCompilerPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container): void
    {
        $containerInfoDump = $container->findDefinition(ServiceAliasContainerInfoRegistry::class);

        $serviceIdsToClassNames = [];
        $publicServiceIds = [];
        foreach ($container->getDefinitions() as $serviceId => $service) {
            $serviceIdsToClassNames[$serviceId] = $service->getClass();
            if ($service->isPublic()) {
                $publicServiceIds[] = $serviceId;
            }
        }
        $containerInfoDump->addMethodCall('setServiceIdsToClassNames', [$serviceIdsToClassNames]);
        $containerInfoDump->addMethodCall('setPublicServiceIds', [$publicServiceIds]);

        $aliasIdsToAliases = [];
        foreach ($container->getAliases() as $aliasId => $alias) {
            $aliasIdsToAliases[$aliasId] = (string)$alias;
        }
        $containerInfoDump->addMethodCall('setAliasIdsToAliases', [$aliasIdsToAliases]);
    }
}
