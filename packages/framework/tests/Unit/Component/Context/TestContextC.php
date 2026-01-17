<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Context;

use Override;
use Shopsys\FrameworkBundle\Component\Context\AbstractContext;

final class TestContextC extends AbstractContext
{
    /**
     * @param array<class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext>> $requiredContexts
     */
    public function __construct(
        private readonly bool $shouldMatch = true,
        private readonly array $requiredContexts = [],
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function matches(): bool
    {
        return $this->shouldMatch;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getRequiredContexts(): array
    {
        return $this->requiredContexts;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getDescription(): string
    {
        return 'Test context C for unit testing';
    }
}
