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

    #[Override]
    public function getDescription(): string
    {
        return 'Scheduled cron job execution';
    }

    #[Override]
    public function getRequiredContexts(): array
    {
        return [ConsoleContext::class];
    }

    #[Override]
    public function matches(): bool
    {
        return $this->consoleHelper->isCommandMatching('shopsys:cron');
    }
}
