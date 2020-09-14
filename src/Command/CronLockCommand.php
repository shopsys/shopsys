<?php

declare(strict_types=1);


namespace App\Command;

use NinjaMutex\Lock\LockInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronLockCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'devops:cron:lock';

    public const CRON_MUTEX_LOCK_NAME = 'cronLocker';
    private const SLEEP_TIME = 600;

    /**
     * @var \NinjaMutex\Lock\LockInterface
     */
    private $lock;

    /**
     * @param \NinjaMutex\Lock\LockInterface $lock
     */
    public function __construct(LockInterface $lock)
    {
        $this->lock = $lock;
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setDescription('This command will lock cron command so crons will not run until this command is terminated.');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->lock->acquireLock(self::CRON_MUTEX_LOCK_NAME, 0) !== true) {
            return 1;
        }

        $output->writeln('Cron Command was locked.');

        while (1) {
            sleep(self::SLEEP_TIME);
        }

        return 0;
    }
}
