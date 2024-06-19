<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Shopsys\FrameworkBundle\Component\Cron\CronFacade;
use Shopsys\FrameworkBundle\Component\Cron\MutexFactory;
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
    /**
     * @param \Shopsys\FrameworkBundle\Component\Cron\CronFacade $cronFacade
     * @param \Shopsys\FrameworkBundle\Component\Cron\MutexFactory $mutexFactory
     */
    public function __construct(
        protected readonly CronFacade $cronFacade,
        protected readonly MutexFactory $mutexFactory,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
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
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $mutexFactory = $this->mutexFactory;
        $cronInstanceNames = $this->cronFacade->getInstanceNames();

        $mutexLockByCronInstance = array_map(
            static fn ($cronInstanceName) => $mutexFactory->getPrefixedCronMutex($cronInstanceName),
            $cronInstanceNames,
        );

        do {
            $isAnyCronRunning = false;

            foreach ($mutexLockByCronInstance as $mutexLock) {
                if ($mutexLock->isLocked() === true) {
                    $isAnyCronRunning = true;
                }
            }
        } while ($isAnyCronRunning === true);

        return Command::SUCCESS;
    }
}
