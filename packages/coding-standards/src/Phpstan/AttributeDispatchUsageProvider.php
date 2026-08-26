<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Phpstan;

use Override;
use ReflectionMethod;
use ShipMonk\PHPStan\DeadCode\Provider\ReflectionBasedMemberUsageProvider;
use ShipMonk\PHPStan\DeadCode\Provider\VirtualUsageData;

class AttributeDispatchUsageProvider extends ReflectionBasedMemberUsageProvider
{
    /**
     * @param string[] $attributeClassNames
     */
    public function __construct(
        protected readonly array $attributeClassNames,
    ) {
    }

    #[Override]
    protected function shouldMarkMethodAsUsed(ReflectionMethod $method): ?VirtualUsageData
    {
        foreach ($this->attributeClassNames as $attributeClassName) {
            if (count($method->getAttributes($attributeClassName)) > 0) {
                return VirtualUsageData::withNote(
                    sprintf('Method is dispatched through the %s attribute', $attributeClassName),
                );
            }
        }

        return null;
    }
}
