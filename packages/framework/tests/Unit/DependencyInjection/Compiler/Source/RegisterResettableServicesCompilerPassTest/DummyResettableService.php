<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\DependencyInjection\Compiler\Source\RegisterResettableServicesCompilerPassTest;

use Override;
use Symfony\Contracts\Service\ResetInterface;

class DummyResettableService implements ResetInterface
{
    public function __construct(
        public readonly string $name = '',
    ) {
    }

    #[Override]
    public function reset(): void
    {
    }
}
