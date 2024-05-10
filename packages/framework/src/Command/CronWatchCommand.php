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
    name: 'deploy:cron:watch',
    description: 'Cron watch command is running until cron instance ends, then is terminated',
)]
class CronWatchCommand extends Command
{
    public function __construct(
        protected readonly CronControlFacade $cronControlFacade,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function configure(): void
    {
        $this->setHelp(
            <<<'EOF'
The <info>%command.name%</info> command runs continuously while any cron is running.
After cron is finished, this command is terminated.

It is used mainly for deployment purposes to monitor cron execution and determine whether is safe to continue with deployment.
EOF,
        );
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->cronControlFacade->waitUntilCronInstancesAreFinished();

        return Command::SUCCESS;
    }
}
