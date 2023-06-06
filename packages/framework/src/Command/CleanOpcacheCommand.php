<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use CacheTool\Adapter\FastCGI;
use CacheTool\CacheTool;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CleanOpcacheCommand extends Command
{
    /**
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected static $defaultName = 'shopsys:clean-opcache';

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setDescription('Clean opcache in PHP-FPM service');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $symfonyStyle->info('Opcache cleaning...');

        $adapter = new FastCGI();
        $cache = CacheTool::factory($adapter);
        $cache->opcache_reset();

        $symfonyStyle->success('Done!');

        return Command::SUCCESS;
    }
}
