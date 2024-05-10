<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Override;
use Shopsys\FrameworkBundle\Component\Cron\CronControlFacade;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'deploy:cron:lock',
    description: 'This command will prevent any crons from running on this machine until it is terminated',
)]
class CronLockCommand extends Command
{
    protected const SLEEP_TIME = 600;
    protected const MAX_LOCK_TIME = 3600;

    public function __construct(
        protected readonly CronControlFacade $cronControlFacade,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->cronControlFacade->lockCron()) {
            return Command::FAILURE;
        }

        $startTime = time();
        $output->writeln('Cron is now locked.');

        while (time() - $startTime < static::MAX_LOCK_TIME) {
            sleep(static::SLEEP_TIME);
        }

        $this->cronControlFacade->unlockCron();
        $output->writeln('Cron lock was released due to timeout.');

        return Command::SUCCESS;
    }
}
