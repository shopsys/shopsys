<?php

declare(strict_types=1);

namespace App\Component\ClassExtension;

use App\FrontendApi\Model\Product\Connection\ProductConnection;
use Shopsys\FrameworkBundle\Component\ClassExtension\ClassExtensionRegistry as BaseClassExtensionRegistry;
use Symfony\Component\Finder\Finder;

/**
 * Hotfix for https://github.com/shopsys/shopsys/issues/2372
 */
class ClassExtensionRegistry extends BaseClassExtensionRegistry
{
    /**
     * temporary solution until https://shopsys.atlassian.net/browse/FWCC-717 is solved
     */
    private const CLASSES_EXCLUDED_FROM_OTHER_MAP = [
        ProductConnection::class => ProductConnection::class,
    ];

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
            if ($this->isExcludedFromOtherClassesExtensionMap($projectClassFqcn) === false && class_exists($projectClassFqcn)) {
                $otherClassesMap[$frontendApiClassFqcn] = $projectClassFqcn;
            }
        }

        return $otherClassesMap;
    }

    /**
     * @param string $fqcn
     * @return bool
     */
    private function isExcludedFromOtherClassesExtensionMap(string $fqcn): bool
    {
        return array_key_exists($fqcn, self::CLASSES_EXCLUDED_FROM_OTHER_MAP);
    }
}
