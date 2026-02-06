<?php

declare(strict_types=1);

namespace Shopsys\Cli\Config;

use Symfony\Component\Console\Style\SymfonyStyle;

interface DomainConfigSectionInterface extends ConfigSectionInterface
{
    /**
     * Collect values interactively
     */
    public function collectInteractive(SymfonyStyle $io, int $domainId): void;
}
