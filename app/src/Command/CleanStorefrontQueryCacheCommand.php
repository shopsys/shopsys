<?php

declare(strict_types=1);

namespace App\Command;

use App\Component\Redis\CleanStorefrontCacheFacade;
use RedisException;
use Shopsys\FrameworkBundle\Command\CommandResultCodes;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanStorefrontQueryCacheCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'shopsys:redis:clean-storefront-query-cache';

    private CleanStorefrontCacheFacade $cleanStorefrontCacheFacade;

    /**
     * @param \App\Component\Redis\CleanStorefrontCacheFacade $cleanStorefrontCacheFacade
     */
    public function __construct(CleanStorefrontCacheFacade $cleanStorefrontCacheFacade)
    {
        $this->cleanStorefrontCacheFacade = $cleanStorefrontCacheFacade;

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
        try {
            $this->cleanStorefrontCacheFacade->cleanStorefrontCache();
        } catch (RedisException $exception) {
            // skip silently – when redis is not available, no cache take place
        }

        return CommandResultCodes::RESULT_OK;
    }
}
