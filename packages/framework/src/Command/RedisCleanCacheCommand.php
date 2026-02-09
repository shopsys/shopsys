<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Override;
use Shopsys\FrameworkBundle\Component\Redis\RedisFacade;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'shopsys:redis:clean-cache',
    description: 'Cleans up redis cache',
)]
class RedisCleanCacheCommand extends Command
{
    /**
     * RedisCleanCacheCommand constructor.
     */
    public function __construct(private readonly RedisFacade $redisFacade)
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->redisFacade->cleanCache();

        return Command::SUCCESS;
    }
}
