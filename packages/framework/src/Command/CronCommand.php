<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use DateTimeZone;
use NinjaMutex\Lock\LockInterface;
use Shopsys\FrameworkBundle\Command\Exception\CronCommandException;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig;
use Shopsys\FrameworkBundle\Component\Cron\CronFacade;
use Shopsys\FrameworkBundle\Component\Cron\MutexFactory;
use Shopsys\FrameworkBundle\Component\DateTimeHelper\DateTimeHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'shopsys:cron',
    description: 'Runs background jobs. Should be executed periodically by system CRON every 5 minutes.',
)]
class CronCommand extends Command
{
    private const OPTION_MODULE = 'module';
    private const OPTION_LIST = 'list';
    private const OPTION_INSTANCE_NAME = 'instance-name';
    private const OPTION_RUN_ALL_SERIALLY = 'run-all-serially';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Cron\CronFacade $cronFacade
     * @param \Shopsys\FrameworkBundle\Component\Cron\MutexFactory $mutexFactory
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameterBag
     * @param \NinjaMutex\Lock\LockInterface $lock
     */
    public function __construct(
        private readonly CronFacade $cronFacade,
        private readonly MutexFactory $mutexFactory,
        private readonly ParameterBagInterface $parameterBag,
        protected readonly LockInterface $lock,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(self::OPTION_LIST, null, InputOption::VALUE_NONE, 'List all Service commands')
            ->addOption(self::OPTION_MODULE, null, InputOption::VALUE_OPTIONAL, 'Service ID')
            ->addOption(
                self::OPTION_INSTANCE_NAME,
                null,
                InputOption::VALUE_REQUIRED,
                'specific cron instance identifier',
            )
            ->addOption(
                self::OPTION_RUN_ALL_SERIALLY,
                null,
                InputOption::VALUE_NONE,
                'run all crons serially (this is not safe to run in production environment)',
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $optionList = $input->getOption(self::OPTION_LIST);
        $optionInstanceName = $input->getOption(self::OPTION_INSTANCE_NAME);
        $optionRunAllSerially = $input->getOption(self::OPTION_RUN_ALL_SERIALLY);

        if ($optionList === true) {
            $this->listAllCronModulesSortedByServiceId($input, $output, $this->cronFacade);

            return Command::SUCCESS;
        }

        if ($this->lock->isLocked(CronLockCommand::CRON_MUTEX_LOCK_NAME)) {
            $output->writeln('Crons are locked with `deploy:cron:lock` command. Nothing to do.');

            return Command::FAILURE;
        }

        if ($optionRunAllSerially === true) {
            foreach ($this->cronFacade->getAll() as $cronModuleConfig) {
                $this->cronFacade->runModuleByServiceId($cronModuleConfig->getServiceId());
            }

            return Command::SUCCESS;
        }

        $instanceName = $optionInstanceName ?? $this->chooseInstance($input, $output);
        $this->runCron($input, $this->cronFacade, $this->mutexFactory, $instanceName);

        return Command::SUCCESS;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Shopsys\FrameworkBundle\Component\Cron\CronFacade $cronFacade
     */
    private function listAllCronModulesSortedByServiceId(
        InputInterface $input,
        OutputInterface $output,
        CronFacade $cronFacade,
    ) {
        $instanceNames = $cronFacade->getInstanceNames();
        $io = new SymfonyStyle($input, $output);

        if (count($instanceNames) === 1) {
            $cronModuleConfigs = $cronFacade->getAll();
            $io->text($this->getCronCommands($cronModuleConfigs));

            return;
        }

        foreach ($instanceNames as $instanceName) {
            $io->section($instanceName);

            $cronModuleConfigs = $cronFacade->getAllForInstance($instanceName);
            $io->text($this->getCronCommands($cronModuleConfigs, true));
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig[] $cronModuleConfigs
     * @param bool $includeInstance
     * @return string[]
     */
    private function getCronCommands(array $cronModuleConfigs, bool $includeInstance = false): array
    {
        uasort(
            $cronModuleConfigs,
            function (CronModuleConfig $cronModuleConfigA, CronModuleConfig $cronModuleConfigB) {
                return strcmp($cronModuleConfigA->getServiceId(), $cronModuleConfigB->getServiceId());
            },
        );

        $commands = [];

        foreach ($cronModuleConfigs as $cronModuleConfig) {
            $command = sprintf(
                'php bin/console %s --%s="%s"',
                $this->getName(),
                self::OPTION_MODULE,
                $cronModuleConfig->getServiceId(),
            );

            if ($includeInstance) {
                $command .= sprintf(' --%s=%s', self::OPTION_INSTANCE_NAME, $cronModuleConfig->getInstanceName());
            }

            $commands[] = $command;
        }

        return $commands;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Shopsys\FrameworkBundle\Component\Cron\CronFacade $cronFacade
     * @param \Shopsys\FrameworkBundle\Component\Cron\MutexFactory $mutexFactory
     * @param string $instanceName
     */
    private function runCron(
        InputInterface $input,
        CronFacade $cronFacade,
        MutexFactory $mutexFactory,
        string $instanceName,
    ) {
        $requestedModuleServiceId = $input->getOption(self::OPTION_MODULE);
        $runAllModules = $requestedModuleServiceId === null;
        $cronInstances = $this->parameterBag->get('cron_instances');
        $instanceRunEveryMin = $cronInstances[$instanceName]['run_every_min'] ?? CronModuleConfig::RUN_EVERY_MIN_DEFAULT;

        if ($instanceRunEveryMin < 0 || $instanceRunEveryMin > 30) {
            $instanceRunEveryMin = CronModuleConfig::RUN_EVERY_MIN_DEFAULT;
        }

        if ($runAllModules) {
            $cronFacade->scheduleModulesByTime(DateTimeHelper::getCurrentRoundedTimeForIntervalAndTimezone($instanceRunEveryMin, $this->getCronTimeZone()));
        }

        $mutex = $mutexFactory->getPrefixedCronMutex($instanceName);

        if (!$mutex->acquireLock(0)) {
            throw new CronCommandException(
                'Cron is locked. Another cron module is already running.',
            );
        }

        if ($runAllModules) {
            $cronFacade->runScheduledModulesForInstance($instanceName);
        } else {
            $cronFacade->runModuleByServiceId($requestedModuleServiceId);
        }
        $mutex->releaseLock();
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return string
     */
    private function chooseInstance(InputInterface $input, OutputInterface $output): string
    {
        $instanceNames = $this->cronFacade->getInstanceNames();

        $defaultInstanceName = in_array(
            CronModuleConfig::DEFAULT_INSTANCE_NAME,
            $instanceNames,
            true,
        ) ? CronModuleConfig::DEFAULT_INSTANCE_NAME : reset(
            $instanceNames,
        );

        if (count($instanceNames) === 1) {
            return $defaultInstanceName;
        }

        $instanceNameChoices = [];

        foreach ($instanceNames as $instanceName) {
            $instanceNameChoices[] = $instanceName;
        }

        $io = new SymfonyStyle($input, $output);

        return $io->choice(
            'There is more than one cron instance. Which instance do you want to use?',
            $instanceNameChoices,
            $defaultInstanceName,
        );
    }

    /**
     * @return \DateTimeZone
     */
    private function getCronTimeZone(): DateTimeZone
    {
        /** @var string|null $cronTimezone */
        $cronTimezone = $this->parameterBag->get('shopsys.cron_timezone');
        $cronTimezone = $cronTimezone ?? date_default_timezone_get();

        return new DateTimeZone($cronTimezone);
    }
}
