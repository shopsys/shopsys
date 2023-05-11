<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use NinjaMutex\Lock\LockInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronLockCommand extends Command
{
    protected static string $defaultName = 'deploy:cron:lock';

    public const CRON_MUTEX_LOCK_NAME = 'cronLocker';

    protected const SLEEP_TIME = 600;
    protected const MAX_LOCK_TIME = 3600;

    /**
     * @param \NinjaMutex\Lock\LockInterface $lock
     */
    public function __construct(
        protected readonly LockInterface $lock,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setDescription('This command will prevent any crons from running on this machine until it is terminated.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock->acquireLock(self::CRON_MUTEX_LOCK_NAME, 0)) {
            return Command::FAILURE;
        }

        $startTime = time();
        $output->writeln('Cron is now locked.');

        while (time() - $startTime < static::MAX_LOCK_TIME) {
            sleep(static::SLEEP_TIME);
        }

        $this->lock->releaseLock(self::CRON_MUTEX_LOCK_NAME);
        $output->writeln('Cron lock was released due to timeout.');

        return Command::SUCCESS;
    }
}
