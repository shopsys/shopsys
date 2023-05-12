<?php

namespace Shopsys\FrameworkBundle\Command;

use RedisException;
use Shopsys\FrameworkBundle\Component\Redis\RedisFacade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CheckRedisCommand extends Command
{
    /**
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnlySniff.ReferenceViaFullyQualifiedName
     */
    protected static $defaultName = 'shopsys:redis:check-availability';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Redis\RedisFacade $redisFacade
     */
    public function __construct(protected readonly RedisFacade $redisFacade)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Checks availability of Redis');
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
