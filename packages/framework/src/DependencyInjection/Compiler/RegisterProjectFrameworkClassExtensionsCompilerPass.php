<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\DependencyInjection\Compiler;

use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionObject;
use Roave\BetterReflection\Reflector\Exception\IdentifierNotFound;
use Shopsys\FrameworkBundle\Component\ClassExtension\ClassExtensionRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class RegisterProjectFrameworkClassExtensionsCompilerPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container): void
    {
        $classExtensionRegistryDefinition = $container->findDefinition(ClassExtensionRegistry::class);

        foreach ($container->getServiceIds() as $serviceId) {
            try {
                $aliasId = (string)$container->getAlias($serviceId);
            } catch (InvalidArgumentException) {
                $aliasId = null;
            }

            try {
                $serviceClass = $container->findDefinition($serviceId)->getClass();
            } catch (ServiceNotFoundException) {
                continue;
            }

            if (!$this->isProjectClass($aliasId) && $this->isProjectClass($serviceClass)) {
                $aliasId = $serviceClass;
            }

            $serviceId = $this->getCorrectServiceIdIfServiceIsNotExtendedByAlias($container, $serviceId, $aliasId);

            if (!(
                $this->isShopsysClassWithAlias($container, $serviceId, $aliasId) ||
                $this->isShopsysClassWithAliasRegisteredAsService($container, $serviceId, $aliasId)
            )) {
                continue;
            }

            $frameworkClassBetterReflection = ReflectionObject::createFromName($serviceId);

            if ($frameworkClassBetterReflection->isInterface() && !$this->isProjectClass($aliasId)) {
                continue;
            }

            $classExtensionRegistryDefinition->addMethodCall('addExtendedService', [$serviceId, $aliasId]);
            $this->addAllVariantsOfServiceToClassExtension($frameworkClassBetterReflection, $classExtensionRegistryDefinition, $aliasId);
        }
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param string $serviceId
     * @param string|null $aliasId
     * @return bool
     */
    private function isShopsysClassWithAlias(ContainerBuilder $container, string $serviceId, ?string $aliasId): bool
    {
        return $this->isShopsysClass($serviceId) && ($container->hasAlias($serviceId) && $this->isProjectClass($aliasId));
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param string $serviceId
     * @param string|null $aliasId
     * @return bool
     */
    private function isShopsysClassWithAliasRegisteredAsService(ContainerBuilder $container, string $serviceId, ?string $aliasId): bool
    {
        if ($aliasId === null || !$this->isShopsysClass($serviceId) || !($this->isProjectClass($aliasId) || $this->isShopsysClass($aliasId))) {
            return false;
        }

        try {
            $container->findDefinition($aliasId);

            return true;
        } catch (ServiceNotFoundException) {
            return false;
        }
    }

    /**
     * @param string $serviceId
     * @return bool
     */
    private function isShopsysClass(string $serviceId): bool
    {
        return str_starts_with($serviceId, 'Shopsys\\') !== false;
    }

    /**
     * @param string|null $serviceId
     * @return bool
     */
    private function isProjectClass(?string $serviceId): bool
    {
        if ($serviceId === null) {
            return false;
        }

        return str_starts_with($serviceId, 'App') !== false;
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionClass $frameworkClassBetterReflection
     * @param \Symfony\Component\DependencyInjection\Definition $classExtensionRegistryDefinition
     * @param string $aliasId
     */
    private function addAllVariantsOfServiceToClassExtension(
        ReflectionClass $frameworkClassBetterReflection,
        Definition $classExtensionRegistryDefinition,
        string $aliasId
    ): void {
        if ($frameworkClassBetterReflection->isInterface()) {
            $className = preg_replace('/Interface$/', '', $frameworkClassBetterReflection->getName());

            if ($className !== $aliasId && class_exists($className)) {
                $classExtensionRegistryDefinition->addMethodCall('addExtendedService', [$className, $aliasId]);
            }
        } else {
            $interfaceName = $frameworkClassBetterReflection->getName() . 'Interface';

            if ($interfaceName !== $aliasId && interface_exists($interfaceName) && in_array($interfaceName, $frameworkClassBetterReflection->getInterfaceNames(), true)) {
                $classExtensionRegistryDefinition->addMethodCall('addExtendedService', [$interfaceName, $aliasId]);
            }
        }
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param string $serviceId
     * @param string|null $aliasId
     * @return string
     */
    private function getCorrectServiceIdIfServiceIsNotExtendedByAlias(ContainerBuilder $container, string $serviceId, ?string $aliasId): string
    {
        if ($this->isProjectClass($aliasId)) {
            try {
                $baseClassName = (string)ReflectionObject::createFromName($serviceId)->getLocatedSource()->getName();

                if ($this->isShopsysClass($baseClassName)) {
                    $serviceId = $baseClassName;
                }
            } catch (IdentifierNotFound) {
            }
        }

        return $serviceId;
    }
}
