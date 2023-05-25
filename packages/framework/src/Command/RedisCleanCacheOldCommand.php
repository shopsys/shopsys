<?php

namespace Shopsys\FrameworkBundle\Command;

use Shopsys\FrameworkBundle\Component\Redis\RedisVersionsFacade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RedisCleanCacheOldCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'shopsys:redis:clean-cache-old';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Redis\RedisVersionsFacade
     */
    private $redisVersionsFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Redis\RedisVersionsFacade $redisVersionsFacade
     */
    public function __construct(RedisVersionsFacade $redisVersionsFacade)
    {
        $this->redisVersionsFacade = $redisVersionsFacade;

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
