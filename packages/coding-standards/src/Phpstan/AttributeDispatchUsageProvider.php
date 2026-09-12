<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Phpstan;

use Override;
use ReflectionException;
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

    /**
     * The attribute may be declared on the method itself, on the method it overrides (attributes are not inherited)
     * or, for __invoke(), on the class
     */
    #[Override]
    protected function shouldMarkMethodAsUsed(ReflectionMethod $method): ?VirtualUsageData
    {
        foreach ($this->attributeClassNames as $attributeClassName) {
            if ($this->hasAttribute($method, $attributeClassName)) {
                return VirtualUsageData::withNote(
                    sprintf('Method is dispatched through the %s attribute', $attributeClassName),
                );
            }
        }

        return null;
    }

    protected function hasAttribute(ReflectionMethod $method, string $attributeClassName): bool
    {
        if ($method->getName() === '__invoke' && count($method->getDeclaringClass()->getAttributes($attributeClassName)) > 0) {
            return true;
        }

        $currentMethod = $method;

        while (true) {
            if (count($currentMethod->getAttributes($attributeClassName)) > 0) {
                return true;
            }

            try {
                $currentMethod = $currentMethod->getPrototype();
            } catch (ReflectionException) {
                return false;
            }
        }
    }
}
