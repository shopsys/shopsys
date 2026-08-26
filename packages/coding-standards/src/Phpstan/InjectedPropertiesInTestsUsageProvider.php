<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Phpstan;

use Override;
use ReflectionProperty;
use ShipMonk\PHPStan\DeadCode\Provider\ReflectionBasedMemberUsageProvider;
use ShipMonk\PHPStan\DeadCode\Provider\VirtualUsageData;
use Zalas\Injector\PHPUnit\TestCase\ServiceContainerTestCase;

class InjectedPropertiesInTestsUsageProvider extends ReflectionBasedMemberUsageProvider
{
    #[Override]
    protected function shouldMarkPropertyAsWritten(ReflectionProperty $property): ?VirtualUsageData
    {
        if (!interface_exists(ServiceContainerTestCase::class)) {
            return null;
        }

        if (!$property->getDeclaringClass()->implementsInterface(ServiceContainerTestCase::class)) {
            return null;
        }

        if (!str_contains($property->getDocComment() ?: '', '@inject')) {
            return null;
        }

        return VirtualUsageData::withNote('Property is written by zalas/phpunit-injector via the @inject annotation');
    }
}
