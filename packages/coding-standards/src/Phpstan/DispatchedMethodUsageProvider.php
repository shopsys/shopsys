<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Phpstan;

use Override;
use ReflectionMethod;
use ShipMonk\PHPStan\DeadCode\Provider\ReflectionBasedMemberUsageProvider;
use ShipMonk\PHPStan\DeadCode\Provider\VirtualUsageData;

class DispatchedMethodUsageProvider extends ReflectionBasedMemberUsageProvider
{
    /**
     * @param array<class-string, string[]> $dispatchedMethodNamesByClassName
     * @param array<class-string, string[]> $dispatchedMethodSuffixesByClassName
     */
    public function __construct(
        protected readonly array $dispatchedMethodNamesByClassName,
        protected readonly array $dispatchedMethodSuffixesByClassName,
    ) {
    }

    #[Override]
    protected function shouldMarkMethodAsUsed(ReflectionMethod $method): ?VirtualUsageData
    {
        $declaringClassName = $method->getDeclaringClass()->getName();

        foreach ($this->dispatchedMethodNamesByClassName as $className => $dispatchedMethodNames) {
            if (!in_array($method->getName(), $dispatchedMethodNames, true)) {
                continue;
            }

            if (is_a($declaringClassName, $className, true)) {
                return VirtualUsageData::withNote(
                    sprintf('Method is dispatched by name on %s instances', $className),
                );
            }
        }

        foreach ($this->dispatchedMethodSuffixesByClassName as $className => $dispatchedMethodSuffixes) {
            foreach ($dispatchedMethodSuffixes as $dispatchedMethodSuffix) {
                if (!str_ends_with($method->getName(), $dispatchedMethodSuffix)) {
                    continue;
                }

                if (is_a($declaringClassName, $className, true)) {
                    return VirtualUsageData::withNote(
                        sprintf('Method is dispatched by its %s suffix on %s instances', $dispatchedMethodSuffix, $className),
                    );
                }
            }
        }

        return null;
    }
}
