<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Context;

use Override;
use Shopsys\FrameworkBundle\Component\Console\ConsoleHelper;

final class CronContext extends AbstractContext
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
        return 'Scheduled cron job execution';
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
        return $this->consoleHelper->isCommandMatching('shopsys:cron');
    }
}
