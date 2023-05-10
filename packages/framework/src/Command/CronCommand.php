<?php

namespace Shopsys\FrameworkBundle\Command;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Shopsys\FrameworkBundle\Command\Exception\CronCommandException;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig;
use Shopsys\FrameworkBundle\Component\Cron\CronFacade;
use Shopsys\FrameworkBundle\Component\Cron\MutexFactory;
use Shopsys\FrameworkBundle\Component\Deprecations\DeprecationHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CronCommand extends Command
{
    private const OPTION_MODULE = 'module';
    private const OPTION_LIST = 'list';
    private const OPTION_INSTANCE_NAME = 'instance-name';

    /**
     * @var string
     */
    protected static $defaultName = 'shopsys:cron';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Cron\CronFacade
     */
    private $cronFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Cron\MutexFactory
     */
    private $mutexFactory;

    /**
     * @var \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface
     */
    private $parameterBag;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Cron\CronFacade $cronFacade
     * @param \Shopsys\FrameworkBundle\Component\Cron\MutexFactory $mutexFactory
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameterBag
     */
    public function __construct(
        CronFacade $cronFacade,
        MutexFactory $mutexFactory,
        ParameterBagInterface $parameterBag
    ) {
        $this->cronFacade = $cronFacade;
        $this->mutexFactory = $mutexFactory;
        $this->parameterBag = $parameterBag;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Runs background jobs. Should be executed periodically by system CRON every 5 minutes.')
            ->addOption(self::OPTION_LIST, null, InputOption::VALUE_NONE, 'List all Service commands')
            ->addOption(self::OPTION_MODULE, null, InputOption::VALUE_OPTIONAL, 'Service ID')
            ->addOption(
                self::OPTION_INSTANCE_NAME,
                null,
                InputOption::VALUE_REQUIRED,
                'specific cron instance identifier'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $optionList = $input->getOption(self::OPTION_LIST);
        $optionInstanceName = $input->getOption(self::OPTION_INSTANCE_NAME);

        if ($optionList === true) {
            $this->listAllCronModulesSortedByServiceId($input, $output, $this->cronFacade);
        } else {
            $instanceName = $optionInstanceName ?? $this->chooseInstance($input, $output);

            $this->runCron($input, $this->cronFacade, $this->mutexFactory, $instanceName);
        }

        return CommandResultCodes::RESULT_OK;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Shopsys\FrameworkBundle\Component\Cron\CronFacade $cronFacade
     */
    private function listAllCronModulesSortedByServiceId(InputInterface $input, OutputInterface $output, CronFacade $cronFacade)
    {
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
            }
        );

        $commands = [];

        foreach ($cronModuleConfigs as $cronModuleConfig) {
            $command = sprintf(
                'php bin/console %s --%s="%s"',
                $this->getName(),
                self::OPTION_MODULE,
                $cronModuleConfig->getServiceId()
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
    private function runCron(InputInterface $input, CronFacade $cronFacade, MutexFactory $mutexFactory, string $instanceName)
    {
        $requestedModuleServiceId = $input->getOption(self::OPTION_MODULE);
        $runAllModules = $requestedModuleServiceId === null;
        $cronInstances = $this->parameterBag->get('cron_instances');
        $instanceRunEveryMin = $cronInstances[$instanceName]['run_every_min'] ?? CronModuleConfig::RUN_EVERY_MIN_DEFAULT;

        if ($instanceRunEveryMin < 0 || $instanceRunEveryMin > 30) {
            $instanceRunEveryMin = CronModuleConfig::RUN_EVERY_MIN_DEFAULT;
        }

        if ($runAllModules) {
            $cronFacade->scheduleModulesByTime($this->getCurrentRoundedTime($instanceRunEveryMin));
        }

        $mutex = $mutexFactory->getPrefixedCronMutex($instanceName);

        if (!$mutex->acquireLock(0)) {
            throw new CronCommandException(
                'Cron is locked. Another cron module is already running.'
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
     * @phpstan-ignore-next-line
     * @param int $runEveryMin
     * @return \DateTimeImmutable
     */
    private function getCurrentRoundedTime(/* int $runEveryMin */)
    {
        $runEveryMin = DeprecationHelper::triggerNewArgumentInMethod(
            __METHOD__,
            '$runEveryMin',
            'int',
            func_get_args(),
            0,
            CronModuleConfig::RUN_EVERY_MIN_DEFAULT,
            true
        );

        $time = new DateTime('now', $this->getCronTimeZone());
        $time->modify('-' . $time->format('s') . ' sec');
        $time->modify('-' . ($time->format('i') % $runEveryMin) . ' min');

        return DateTimeImmutable::createFromMutable($time);
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
            true
        ) ? CronModuleConfig::DEFAULT_INSTANCE_NAME : reset(
            $instanceNames
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
            $defaultInstanceName
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
