<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ClassExtension;

use ErrorException;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflector\DefaultReflector;
use Roave\BetterReflection\SourceLocator\Type\SingleFileSourceLocator;
use Symfony\Component\Finder\Finder;

class ClassExtensionRegistry
{
    /**
     * @var string[]
     */
    protected array $serviceExtensionMap = [];

    /**
     * @var string[]
     */
    protected array $otherClassesExtensionMap = [];

    /**
     * @param string[] $entityExtensionMap
     * @param array<int, array{name: string, path: string, namespace: string, app_namespace: string}> $packagesRegistry
     */
    public function __construct(
        protected readonly array $entityExtensionMap,
        protected readonly array $packagesRegistry,
    ) {
        $this->otherClassesExtensionMap = $this->getOtherClassesExtensionMap();
    }

    /**
     * @param string $parentClassName
     * @param string $childClassName
     */
    public function addExtendedService(string $parentClassName, string $childClassName): void
    {
        if (!array_key_exists($parentClassName, $this->serviceExtensionMap)) {
            $this->serviceExtensionMap[$parentClassName] = $childClassName;
        }
    }

    /**
     * Other classes that are not entities or registered in service extension map
     * I.e. data objects and controllers (other class types can by added by modifying the finder if necessary)
     *
     * @return string[]
     */
    protected function getOtherClassesExtensionMap(): array
    {
        $otherClassesMap = [];

        foreach ($this->packagesRegistry as $package) {
            if (!is_dir($package['path'] . '/src')) {
                continue;
            }

            $finder = Finder::create()
                ->files()
                ->ignoreUnreadableDirs()
                ->in($package['path'] . '/src')
                ->name('/.*\.php$/');

            /** @var \Symfony\Component\Finder\SplFileInfo $file */
            foreach ($finder as $file) {
                try {
                    $packageClassFqcn = $this->getFqcn($file->getPathname());
                } catch (ErrorException) {
                    continue;
                }

                $projectClassFqcn = str_replace($package['namespace'], $package['app_namespace'], $packageClassFqcn);

                if (class_exists($projectClassFqcn)) {
                    $otherClassesMap[$packageClassFqcn] = $projectClassFqcn;
                }
            }
        }

        return $otherClassesMap;
    }

    /**
     * @param string $pathname
     * @return string
     */
    protected function getFqcn(string $pathname): string
    {
        $astLocator = (new BetterReflection())->astLocator();
        $reflector = new DefaultReflector(new SingleFileSourceLocator($pathname, $astLocator));

        return $reflector->reflectAllClasses()[0]->getName();
    }

    /**
     * @return string[]
     */
    public function getClassExtensionMap(): array
    {
        return $this->serviceExtensionMap + $this->entityExtensionMap + $this->otherClassesExtensionMap;
    }
}
