<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\DependencyInjection\Compiler;

use Shopsys\FrameworkBundle\Component\ClassExtension\ClassExtensionRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RegisterProjectFrameworkClassExtensionsCompilerPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container): void
    {
        $classExtensionRegistryDefinition = $container->findDefinition(ClassExtensionRegistry::class);

        foreach ($container->getServiceIds() as $serviceId) {
            if ($this->isFrameworkClassWithAlias($container, $serviceId)) {
                $aliasId = (string)$container->getAlias($serviceId);

                if ($this->isProjectClass($aliasId)) {
                    $classExtensionRegistryDefinition->addMethodCall('addExtendedService', [$serviceId, $aliasId]);
                }
            }
        }
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param string $serviceId
     * @return bool
     */
    private function isFrameworkClassWithAlias(ContainerBuilder $container, string $serviceId): bool
    {
        return strpos($serviceId, 'Shopsys\FrameworkBundle') !== false && $container->hasAlias($serviceId);
    }

    /**
     * @param string $serviceId
     * @return bool
     */
    private function isProjectClass(string $serviceId): bool
    {
        return strpos($serviceId, 'App') !== false;
    }
}
