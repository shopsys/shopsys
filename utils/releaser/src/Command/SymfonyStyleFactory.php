<?php

declare(strict_types=1);

namespace Shopsys\Releaser\Command;

use Shopsys\Releaser\Exception\ShouldNotHappenException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SymfonyStyleFactory
{
    protected SymfonyStyle $symfonyStyle;

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return \Symfony\Component\Console\Style\SymfonyStyle
     */
    public function createAndStoreSymfonyStyle(InputInterface $input, OutputInterface $output): SymfonyStyle
    {
        $this->symfonyStyle = new SymfonyStyle($input, $output);

        return $this->symfonyStyle;
    }

    /**
     * @return \Symfony\Component\Console\Style\SymfonyStyle
     */
    public function getPreviouslyCreatedSymfonyStyle(): SymfonyStyle
    {
        if (!isset($this->symfonyStyle)) {
            throw new ShouldNotHappenException('SymfonyStyle was not created yet.');
        }

        return $this->symfonyStyle;
    }
}
