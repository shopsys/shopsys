<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Context;

use Override;
use Shopsys\FrameworkBundle\Component\Console\ConsoleHelper;

final class ConsumerContext extends AbstractContext
{
    public function __construct(
        private readonly ConsoleHelper $consoleHelper,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getDescription(): string
    {
        return 'Message queue consumer execution';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getRequiredContexts(): array
    {
        return [ConsoleContext::class];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function matches(): bool
    {
        return $this->consoleHelper->isCommandMatching('messenger:consume');
    }
}
