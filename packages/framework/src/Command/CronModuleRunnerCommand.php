<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Override;
use Shopsys\FrameworkBundle\Component\Cron\CronModuleExecutor;
use Shopsys\FrameworkBundle\Component\Cron\CronModuleRunnerFacade;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: self::COMMAND_NAME,
    description: 'Runs a single cron module in isolated internal process.',
    hidden: true,
)]
class CronModuleRunnerCommand extends Command
{
    public const string COMMAND_NAME = 'shopsys:cron:module-runner';

    protected const string ARGUMENT_MODULE = 'module';
    protected const string OPTION_INSTANCE_NAME = 'instance-name';
    protected const string OPTION_RUN_ID = 'run-id';

    public function __construct(
        protected readonly CronModuleRunnerFacade $cronModuleRunnerFacade,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this->addArgument(self::ARGUMENT_MODULE, InputArgument::REQUIRED, 'Module to run (service ID).');
        $this->addOption(self::OPTION_INSTANCE_NAME, null, InputOption::VALUE_REQUIRED, 'Cron instance name for log context.');
        $this->addOption(self::OPTION_RUN_ID, null, InputOption::VALUE_REQUIRED, 'Cron run ID for log context.');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $requestedModuleServiceId = $input->getArgument(self::ARGUMENT_MODULE);
        $instanceName = $input->getOption(self::OPTION_INSTANCE_NAME);
        $runId = $input->getOption(self::OPTION_RUN_ID);

        $runStatus = $this->cronModuleRunnerFacade->runModuleByServiceIdInContext(
            $requestedModuleServiceId,
            is_string($instanceName) ? $instanceName : null,
            is_string($runId) ? $runId : null,
        );

        return $runStatus === CronModuleExecutor::RUN_STATUS_ERROR ? Command::FAILURE : Command::SUCCESS;
    }
}
