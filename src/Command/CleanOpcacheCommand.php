<?php

declare(strict_types=1);

namespace App\Command;

use CacheTool\Adapter\FastCGI;
use CacheTool\CacheTool;
use Shopsys\FrameworkBundle\Command\CommandResultCodes;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CleanOpcacheCommand extends Command
{
    /**
     * @var string
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
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $symfonyStyle->comment('Opcache cleaning...');

        $adapter = new FastCGI();
        $cache = CacheTool::factory($adapter);
        $cache->opcache_reset();

        $symfonyStyle->success('Done!');

        return CommandResultCodes::RESULT_OK;
    }
}
