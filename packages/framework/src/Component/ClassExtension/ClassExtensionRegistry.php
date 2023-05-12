<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ClassExtension;

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
     * @param string $frameworkRootDir
     */
    public function __construct(protected readonly array $entityExtensionMap = [], protected readonly string $frameworkRootDir)
    {
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
        $finder = Finder::create()
            ->files()
            ->ignoreUnreadableDirs()
            ->in($this->frameworkRootDir . '/src')
            ->name('/.*(Data|Controller)\.php/');

        $otherClassesMap = [];
        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder as $file) {
            $frameworkClassFqcn = $this->getFqcn($file->getPathname());
            $projectClassFqcn = str_replace('Shopsys\FrameworkBundle', 'App', $frameworkClassFqcn);

            if (class_exists($projectClassFqcn)) {
                $otherClassesMap[$frameworkClassFqcn] = $projectClassFqcn;
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
