<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Override;
use Shopsys\FrameworkBundle\Component\Maintenance\MaintenanceModeFacade;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'deploy:maintenance',
    description: 'Enable or disable maintenance mode',
)]
class MaintenanceModeCommand extends Command
{
    /**
     * @var string
     */
    protected const string ACTION_ARGUMENT = 'action';

    public function __construct(
        protected readonly MaintenanceModeFacade $maintenanceModeFacade,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function configure(): void
    {
        $this->addArgument(
            self::ACTION_ARGUMENT,
            InputArgument::REQUIRED,
            'Set action to enable or disable maintenance mode (enable/disable)',
        );
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyleIo = new SymfonyStyle($input, $output);

        switch ($input->getArgument(self::ACTION_ARGUMENT)) {
            case 'enable':
                $this->enableMaintenanceMode($symfonyStyleIo);

                return Command::SUCCESS;
            case 'disable':
                $this->disableMaintenanceMode($symfonyStyleIo);

                return Command::SUCCESS;
            default:
                $symfonyStyleIo->error('Invalid action. Allowed actions are enable/disable');

                return Command::INVALID;
        }
    }

    public function enableMaintenanceMode(SymfonyStyle $symfonyStyleIo): void
    {
        $this->maintenanceModeFacade->enable();
        $symfonyStyleIo->note('Maintenance mode was enabled');
    }

    public function disableMaintenanceMode(SymfonyStyle $symfonyStyleIo): void
    {
        if ($this->maintenanceModeFacade->isEnabled()) {
            $this->maintenanceModeFacade->disable();
            $symfonyStyleIo->note('Maintenance mode was disabled');
        }
    }
}
