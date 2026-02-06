<?php

declare(strict_types=1);

namespace Shopsys\Cli\Config;

use Symfony\Component\Console\Style\SymfonyStyle;

interface ProjectConfigSectionInterface extends ConfigSectionInterface
{
    /**
     * Collect values interactively
     */
    public function collectInteractive(SymfonyStyle $io): void;
}
