<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\DependencyInjection\Compiler;

use Shopsys\FrameworkBundle\Component\ServiceAliasContainerInfoRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class SaveServiceAliasContainerInfoCompilerPass implements CompilerPassInterface
{
    protected const TAGS_OF_IMPLICITLY_PUBLIC_SERVICES = [
        'overblog_graphql.resolver',
        'overblog_graphql.mutation',
    ];

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container): void
    {
        $containerInfoDump = $container->findDefinition(ServiceAliasContainerInfoRegistry::class);

        $serviceIdsToClassNames = [];
        $publicServiceIds = [];
        $serviceLocatorIds = [];
        foreach ($container->getDefinitions() as $serviceId => $service) {
            $serviceIdsToClassNames[$serviceId] = $service->getClass();
            if ($this->shouldBePublic($service)) {
                $publicServiceIds[] = $serviceId;
            }
            if ($service->hasTag('container.service_locator')) {
                $serviceLocatorIds[] = $serviceId;
            }
        }
        $containerInfoDump->addMethodCall('setServiceIdsToClassNames', [$serviceIdsToClassNames]);
        $containerInfoDump->addMethodCall('setPublicServiceIds', [$publicServiceIds]);
        $containerInfoDump->addMethodCall('setServiceLocators', array_map(function ($serviceLocatorId) {
            return new Reference($serviceLocatorId);
        }, $serviceLocatorIds));

        $aliasIdsToAliases = [];
        foreach ($container->getAliases() as $aliasId => $alias) {
            $aliasIdsToAliases[$aliasId] = (string)$alias;
        }
        $containerInfoDump->addMethodCall('setAliasIdsToAliases', [$aliasIdsToAliases]);
    }

    /**
     * @param \Symfony\Component\DependencyInjection\Definition $service
     * @return bool
     */
    protected function shouldBePublic(Definition $service): bool
    {
        if ($service->isPublic()) {
            return true;
        }

        // There are some tagged services that are automagically declared public, couldn't find a better solution
        foreach (array_keys($service->getTags()) as $keyName) {
            if (in_array($keyName, static::TAGS_OF_IMPLICITLY_PUBLIC_SERVICES, true)) {
                return true;
            }
        }

        return false;
    }
}
