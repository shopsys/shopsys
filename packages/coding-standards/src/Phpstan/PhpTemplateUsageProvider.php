<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Phpstan;

use Override;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionProperty;
use ShipMonk\PHPStan\DeadCode\Provider\ReflectionBasedMemberUsageProvider;
use ShipMonk\PHPStan\DeadCode\Provider\VirtualUsageData;

class PhpTemplateUsageProvider extends ReflectionBasedMemberUsageProvider
{
    use FileFinderTrait;

    /**
     * @var array<string, true>|null
     */
    protected ?array $accessedMemberNames = null;

    /**
     * @param string[] $templateDirectoryPaths
     */
    public function __construct(
        protected readonly array $templateDirectoryPaths,
    ) {
    }

    #[Override]
    protected function shouldMarkMethodAsUsed(ReflectionMethod $method): ?VirtualUsageData
    {
        $this->initializeReferences();

        if (isset($this->accessedMemberNames[strtolower($method->getName())])) {
            return VirtualUsageData::withNote('Method is accessed from a code generation template');
        }

        return null;
    }

    #[Override]
    protected function shouldMarkConstantAsUsed(ReflectionClassConstant $constant): ?VirtualUsageData
    {
        $this->initializeReferences();

        if (isset($this->accessedMemberNames[strtolower($constant->getName())])) {
            return VirtualUsageData::withNote('Constant is accessed from a code generation template');
        }

        return null;
    }

    #[Override]
    protected function shouldMarkPropertyAsRead(ReflectionProperty $property): ?VirtualUsageData
    {
        $this->initializeReferences();

        if (isset($this->accessedMemberNames[strtolower($property->getName())])) {
            return VirtualUsageData::withNote('Property is accessed from a code generation template');
        }

        return null;
    }

    protected function initializeReferences(): void
    {
        if ($this->accessedMemberNames !== null) {
            return;
        }

        $this->accessedMemberNames = [];

        foreach ($this->findFilePathsBySuffix($this->templateDirectoryPaths, '.tpl.php') as $templateFilePath) {
            $templateContent = file_get_contents($templateFilePath);

            if ($templateContent === false) {
                continue;
            }

            if (preg_match_all('~(?:->|::)\s*([a-zA-Z_]\w*)~', $templateContent, $accessMatches) === false) {
                continue;
            }

            foreach ($accessMatches[1] as $accessedMemberName) {
                if ($accessedMemberName === 'class') {
                    continue;
                }

                $this->accessedMemberNames[strtolower($accessedMemberName)] = true;
            }
        }
    }
}
