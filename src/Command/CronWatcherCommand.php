<?php

declare(strict_types=1);


namespace App\Command;

use Shopsys\FrameworkBundle\Component\Cron\CronFacade;
use Shopsys\FrameworkBundle\Component\Cron\MutexFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronWatcherCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'devops:cron:watch';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Cron\CronFacade
     */
    private $cronFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Cron\MutexFactory
     */
    private $mutexFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Cron\CronFacade $cronFacade
     * @param \Shopsys\FrameworkBundle\Component\Cron\MutexFactory $mutexFactory
     */
    public function __construct(CronFacade $cronFacade, MutexFactory $mutexFactory)
    {
        $this->cronFacade = $cronFacade;
        $this->mutexFactory = $mutexFactory;

        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setDescription('Cron Watcher waits until cron instance ends.');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mutexFactory = $this->mutexFactory;
        $cronInstanceNames = $this->cronFacade->getInstanceNames();

        $mutexLockByCronInstance = array_map(function ($cronInstanceName) use ($mutexFactory) {
            return $mutexFactory->getPrefixedCronMutex($cronInstanceName);
        }, $cronInstanceNames);

        do {
            $areCronsRunning = false;

            foreach ($mutexLockByCronInstance as $mutexLock) {
                if ($mutexLock->isLocked() === true) {
                    $areCronsRunning = true;
                }
            }
        } while ($areCronsRunning === true);

        return 0;
    }
}
