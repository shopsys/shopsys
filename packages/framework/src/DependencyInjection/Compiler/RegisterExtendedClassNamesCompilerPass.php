<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\DependencyInjection\Compiler;

use Override;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

final class RegisterExtendedClassNamesCompilerPass implements CompilerPassInterface
{
    public const string EXTENDED_CLASS_NAMES_PARAMETER = 'shopsys.class_extension.extended_class_names';
    public const string ENTITY_EXTENSION_MAP_PARAMETER = 'shopsys.entity_extension.map';
    public const string PACKAGES_REGISTRY_PARAMETER = 'shopsys.packages.registry';

    private const string INTERFACE_SUFFIX = 'Interface';

    public function __construct(
        private readonly string $frameworkNamespacePrefix = 'Shopsys\\',
    ) {
    }

    #[Override]
    public function process(ContainerBuilder $container): void
    {
        $extendedClassNamesByClassName = $this->getServiceExtensionMap($container)
            + $this->getEntityExtensionMap($container)
            + $this->getNamingConventionExtensionMap($container);

        $container->setParameter(self::EXTENDED_CLASS_NAMES_PARAMETER, $extendedClassNamesByClassName);
    }

    /**
     * @return array<class-string, class-string>
     */
    private function getServiceExtensionMap(ContainerBuilder $container): array
    {
        $extendedClassNamesByClassName = [];

        foreach ($container->getServiceIds() as $serviceId) {
            if (!$this->isFrameworkClass($serviceId)) {
                continue;
            }

            $isInterface = interface_exists($serviceId);

            if (!$isInterface && !class_exists($serviceId)) {
                continue;
            }

            $resolvedClassName = $this->findResolvedClassName($container, $serviceId);

            if ($resolvedClassName === null || $resolvedClassName === $serviceId || !is_a($resolvedClassName, $serviceId, true)) {
                continue;
            }

            if ($isInterface && $this->isFrameworkClass($resolvedClassName)) {
                continue;
            }

            $extendedClassNamesByClassName[$serviceId] = $resolvedClassName;
            $extendedClassNamesByClassName += $this->getInterfaceVariantMap($serviceId, $resolvedClassName, $isInterface);
        }

        return $extendedClassNamesByClassName;
    }

    /**
     * The annotations tooling replaces both the interface and the class it names, so a mapping registered for one of them applies to the other as well
     *
     * @return array<class-string, class-string>
     */
    private function getInterfaceVariantMap(string $serviceId, string $resolvedClassName, bool $isInterface): array
    {
        if ($isInterface) {
            $className = preg_replace('/' . self::INTERFACE_SUFFIX . '$/', '', $serviceId);

            if ($className !== $resolvedClassName && class_exists($className) && is_a($resolvedClassName, $className, true)) {
                return [$className => $resolvedClassName];
            }

            return [];
        }

        $interfaceName = $serviceId . self::INTERFACE_SUFFIX;

        if ($this->isFrameworkClass($resolvedClassName)) {
            return [];
        }

        if ($interfaceName !== $resolvedClassName && interface_exists($interfaceName) && is_a($serviceId, $interfaceName, true)) {
            return [$interfaceName => $resolvedClassName];
        }

        return [];
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

    /**
     * A project class extends a package class by naming convention when it lives under the package's app namespace at the same relative name
     *
     * @return array<class-string, class-string>
     */
    private function getNamingConventionExtensionMap(ContainerBuilder $container): array
    {
        if (!$container->hasParameter(self::PACKAGES_REGISTRY_PARAMETER)) {
            return [];
        }

        $extendedClassNamesByClassName = [];
        $packagesRegistry = $container->getParameterBag()->resolveValue($container->getParameter(self::PACKAGES_REGISTRY_PARAMETER));

        foreach ($packagesRegistry as $package) {
            $sourceDirectory = $package['path'] . '/src';

            if (!is_dir($sourceDirectory)) {
                continue;
            }

            $files = new RegexIterator(
                new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourceDirectory, RecursiveDirectoryIterator::SKIP_DOTS)),
                '~\.php$~',
            );

            foreach ($files as $file) {
                $relativeClassName = str_replace('/', '\\', substr($file->getPathname(), strlen($sourceDirectory) + 1, -strlen('.php')));
                $packageClassName = $package['namespace'] . '\\' . $relativeClassName;
                $projectClassName = $package['app_namespace'] . '\\' . $relativeClassName;

                if (class_exists($projectClassName) && get_parent_class($projectClassName) === $packageClassName) {
                    $extendedClassNamesByClassName[$packageClassName] = $projectClassName;
                }
            }
        }

        return $extendedClassNamesByClassName;
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

    private function isFrameworkClass(string $className): bool
    {
        return str_starts_with($className, $this->frameworkNamespacePrefix);
    }
}
