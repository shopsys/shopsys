<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Shopsys\FrameworkBundle\Component\Maintenance\MaintenanceModeSubscriber;
use Shopsys\FrameworkBundle\Component\Redis\RedisClientFacade;
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
    private const ACTION_ARGUMENT = 'action';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Redis\RedisClientFacade $redisClientFacade
     */
    public function __construct(
        protected readonly RedisClientFacade $redisClientFacade,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * @param \Symfony\Component\Console\Style\SymfonyStyle $symfonyStyleIo
     */
    public function enableMaintenanceMode(SymfonyStyle $symfonyStyleIo): void
    {
        $this->redisClientFacade->save(MaintenanceModeSubscriber::MAINTENANCE_KEY, true);
        $symfonyStyleIo->note('Maintenance mode was enabled');
    }

    /**
     * @param \Symfony\Component\Console\Style\SymfonyStyle $symfonyStyleIo
     */
    public function disableMaintenanceMode(SymfonyStyle $symfonyStyleIo): void
    {
        if ($this->redisClientFacade->contains(MaintenanceModeSubscriber::MAINTENANCE_KEY)) {
            $this->redisClientFacade->delete(MaintenanceModeSubscriber::MAINTENANCE_KEY);
            $symfonyStyleIo->note('Maintenance mode was disabled');
        }
    }
}
