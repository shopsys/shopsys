<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Context;

use Override;
use Shopsys\FrameworkBundle\Component\Context\AbstractContext;

final class TestContextB extends AbstractContext
{
    /**
     * @param array<class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext>> $requiredContexts
     */
    public function __construct(
        private readonly bool $shouldMatch = true,
        private readonly array $requiredContexts = [],
    ) {
    }

    #[Override]
    public function matches(): bool
    {
        return $this->shouldMatch;
    }

    #[Override]
    public function getRequiredContexts(): array
    {
        return $this->requiredContexts;
    }

    #[Override]
    public function getDescription(): string
    {
        return 'Test context B for unit testing';
    }
}
