<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\DependencyInjection\Compiler;

use Override;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

final class RegisterExtendedClassNamesCompilerPass implements CompilerPassInterface
{
    public const string EXTENDED_CLASS_NAMES_PARAMETER = 'shopsys.class_extension.extended_class_names';
    public const string ENTITY_EXTENSION_MAP_PARAMETER = 'shopsys.entity_extension.map';

    #[Override]
    public function process(ContainerBuilder $container): void
    {
        $extendedClassNamesByClassName = $this->getEntityExtensionMap($container);

        foreach ($container->getServiceIds() as $serviceId) {
            if (!class_exists($serviceId)) {
                continue;
            }

            $resolvedClassName = $this->findResolvedClassName($container, $serviceId);

            if ($resolvedClassName !== null && $resolvedClassName !== $serviceId && is_a($resolvedClassName, $serviceId, true)) {
                $extendedClassNamesByClassName[$serviceId] = $resolvedClassName;
            }
        }

        $container->setParameter(self::EXTENDED_CLASS_NAMES_PARAMETER, $extendedClassNamesByClassName);
    }

    /**
     * @return array<class-string, class-string>
     */
    private function getEntityExtensionMap(ContainerBuilder $container): array
    {
        if (!$container->hasParameter(self::ENTITY_EXTENSION_MAP_PARAMETER)) {
            return [];
        }

        return $container->getParameter(self::ENTITY_EXTENSION_MAP_PARAMETER);
    }

    private function findResolvedClassName(ContainerBuilder $container, string $serviceId): ?string
    {
        try {
            $definition = $container->findDefinition($serviceId);
        } catch (ServiceNotFoundException) {
            return null;
        }

        $className = $container->getParameterBag()->resolveValue($definition->getClass());

        return is_string($className) ? $className : null;
    }
}
