<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use RedisException;
use Shopsys\FrameworkBundle\Component\Redis\RedisFacade;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'shopsys:redis:check-availability',
    description: 'Checks availability of Redis',
)]
class CheckRedisCommand extends Command
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Redis\RedisFacade $redisFacade
     */
    public function __construct(protected readonly RedisFacade $redisFacade)
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->comment('Checks availability of Redis...');

        try {
            $this->redisFacade->pingAllClients();
            $io->success('Redis is available.');
        } catch (RedisException $e) {
            $io->error('Redis is not available.');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
