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
    protected $otherClassesExtensionMap = [];

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
        $this->otherClassesExtensionMap = $this->getOtherClassesExtensionMap();
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
        foreach ($finder as $file) {
            /** @var \Symfony\Component\Finder\SplFileInfo $file */
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
        $reflector = new ClassReflector(new SingleFileSourceLocator($pathname, $astLocator));
        return $reflector->getAllClasses()[0]->getName();
    }

    /**
     * @return string[]
     */
    public function getClassExtensionMap(): array
    {
        return $this->serviceExtensionMap + $this->entityExtensionMap + $this->otherClassesExtensionMap;
    }
}
