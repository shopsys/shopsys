<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Shopsys\FrameworkBundle\Component\Redis\RedisVersionsFacade;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'shopsys:redis:clean-cache-old')]
class RedisCleanCacheOldCommand extends Command
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Redis\RedisVersionsFacade $redisVersionsFacade
     */
    public function __construct(private readonly RedisVersionsFacade $redisVersionsFacade)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Cleans up redis cache for previous build versions');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->redisVersionsFacade->cleanOldCache();

        return Command::SUCCESS;
    }
}
