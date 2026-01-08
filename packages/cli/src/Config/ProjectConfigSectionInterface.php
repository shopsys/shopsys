<?php

declare(strict_types=1);

namespace Shopsys\Cli\Config;

use Symfony\Component\Console\Style\SymfonyStyle;

interface ProjectConfigSectionInterface extends ConfigSectionInterface
{
    /**
     * Collect values interactively
     *
     * @param \Symfony\Component\Console\Style\SymfonyStyle $io
     */
    public function collectInteractive(SymfonyStyle $io): void;
}
