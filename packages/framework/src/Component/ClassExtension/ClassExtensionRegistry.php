<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ClassExtension;

use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflector\ClassReflector;
use Roave\BetterReflection\SourceLocator\Type\SingleFileSourceLocator;
use Symfony\Component\Finder\Finder;

class ClassExtensionRegistry
{
    /**
     * @var string[]
     */
    protected $entityExtensionMap = [];

    /**
     * @var string[]
     */
    protected $serviceExtensionMap = [];

    /**
     * @var string[]
     */
    protected $dataObjectExtensionMap = [];

    /**
     * @var string
     */
    protected $frameworkRootDir;

    /**
     * @param string[] $entityExtensionMap
     * @param string $frameworkRootDir
     */
    public function __construct(array $entityExtensionMap, string $frameworkRootDir)
    {
        $this->entityExtensionMap = $entityExtensionMap;
        $this->frameworkRootDir = $frameworkRootDir;
        $this->dataObjectExtensionMap = $this->getDataObjectExtensionMap();
    }

    /**
     * @param string $parentClassName
     * @param string $childClassName
     */
    public function addExtendedService(string $parentClassName, string $childClassName): void
    {
        $this->serviceExtensionMap[$parentClassName] = $childClassName;
    }

    /**
     * @return string[]
     */
    protected function getDataObjectExtensionMap(): array
    {
        $finder = Finder::create()
            ->files()
            ->ignoreUnreadableDirs()
            ->in($this->frameworkRootDir)
            ->name('/.*Data\.php/');

        $dataObjectsMap = [];
        foreach ($finder as $file) {
            /** @var \Symfony\Component\Finder\SplFileInfo $file */
            $frameworkClassFqcn = $this->getFqcn($file->getPathname());
            $projectClassFqcn = str_replace('Shopsys\FrameworkBundle', 'Shopsys\ShopBundle', $frameworkClassFqcn);
            if (class_exists($projectClassFqcn)) {
                $dataObjectsMap[$frameworkClassFqcn] = $projectClassFqcn;
            }
        }

        return $dataObjectsMap;
    }

    /**
     * @param string $pathname
     * @return string
     */
    protected function getFqcn(string $pathname): string
    {
        $astLocator = (new BetterReflection())->astLocator();
        $reflector = new ClassReflector(new SingleFileSourceLocator($pathname, $astLocator));
        return $reflector->getAllClasses()[0]->getName();
    }

    /**
     * @return string[]
     */
    public function getClassExtensionMap(): array
    {
        return $this->serviceExtensionMap + $this->entityExtensionMap + $this->dataObjectExtensionMap;
    }
}
