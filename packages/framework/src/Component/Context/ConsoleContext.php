<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Context;

use Override;
use Shopsys\FrameworkBundle\Component\Console\ConsoleHelper;

final class ConsoleContext extends AbstractContext
{
    public function __construct(
        private readonly ConsoleHelper $consoleHelper,
    ) {
    }

    #[Override]
    public function getDescription(): string
    {
        return 'Console command execution';
    }

    #[Override]
    public function matches(): bool
    {
        return $this->consoleHelper->isConsole();
    }
}
