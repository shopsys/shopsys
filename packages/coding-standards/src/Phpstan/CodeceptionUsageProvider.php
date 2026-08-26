<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Phpstan;

use Override;
use ReflectionMethod;
use ShipMonk\PHPStan\DeadCode\Provider\ReflectionBasedMemberUsageProvider;
use ShipMonk\PHPStan\DeadCode\Provider\VirtualUsageData;

class CodeceptionUsageProvider extends ReflectionBasedMemberUsageProvider
{
    #[Override]
    protected function shouldMarkMethodAsUsed(ReflectionMethod $method): ?VirtualUsageData
    {
        if (!$method->isPublic()) {
            return null;
        }

        $declaringClass = $method->getDeclaringClass();

        if (str_ends_with($declaringClass->getName(), 'Cest')) {
            return VirtualUsageData::withNote('Cest methods are invoked by the Codeception runner');
        }

        if (class_exists('Codeception\Module') && $declaringClass->isSubclassOf('Codeception\Module')) {
            return VirtualUsageData::withNote('Codeception module methods are proxied onto the actor by generated actions');
        }

        return null;
    }
}
