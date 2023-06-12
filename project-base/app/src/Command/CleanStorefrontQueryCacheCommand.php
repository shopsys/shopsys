<?php

declare(strict_types=1);

namespace App\Command;

use App\Component\Redis\CleanStorefrontCacheFacade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanStorefrontQueryCacheCommand extends Command
{
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string
     */
    protected static $defaultName = 'shopsys:redis:clean-storefront-query-cache';

    /**
     * @param \App\Component\Redis\CleanStorefrontCacheFacade $cleanStorefrontCacheFacade
     */
    public function __construct(private CleanStorefrontCacheFacade $cleanStorefrontCacheFacade)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Cleans up storefront query cache');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache();

        return Command::SUCCESS;
    }
}
