<?php

declare(strict_types=1);

namespace App\Component\ClassExtension;

use Shopsys\FrameworkBundle\Component\ClassExtension\ClassExtensionRegistry as BaseClassExtensionRegistry;
use Symfony\Component\Finder\Finder;

/**
 * Hotfix for https://github.com/shopsys/shopsys/issues/2372
 */
class ClassExtensionRegistry extends BaseClassExtensionRegistry
{
    /**
     * @inheritDoc
     */
    protected function getOtherClassesExtensionMap(): array
    {
        $otherClassesMap = parent::getOtherClassesExtensionMap();

        $finder = Finder::create()
            ->files()
            ->ignoreUnreadableDirs()
            ->in($this->frameworkRootDir . '/../frontend-api/src')
            ->name('/.*\.php/');

        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder as $file) {
            $frontendApiClassFqcn = $this->getFqcn($file->getPathname());
            $projectClassFqcn = str_replace('Shopsys\FrontendApiBundle', 'App\FrontendApi', $frontendApiClassFqcn);
            if (class_exists($projectClassFqcn)) {
                $otherClassesMap[$frontendApiClassFqcn] = $projectClassFqcn;
            }
        }

        return $otherClassesMap;
    }
}
