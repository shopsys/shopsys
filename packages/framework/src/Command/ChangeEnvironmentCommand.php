<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Exception;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentFileSetting;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'shopsys:environment:change',
    description: 'Change the application environment',
)]
class ChangeEnvironmentCommand extends Command
{
    private const ARG_ENVIRONMENT = 'environment';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Environment\EnvironmentFileSetting $environmentFileSetting
     */
    public function __construct(private readonly EnvironmentFileSetting $environmentFileSetting)
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->addArgument(self::ARG_ENVIRONMENT, InputArgument::OPTIONAL, 'The target environment');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $targetEnvironment = $input->getArgument(self::ARG_ENVIRONMENT);

        if ($targetEnvironment === null && $input->isInteractive()) {
            $targetEnvironment = $io->choice('What environment do you want to set?', EnvironmentType::ALL);
        }

        if ($targetEnvironment === null) {
            throw new Exception(
                'The target environment cannot be empty. Please run this command in interactive mode or set it via an argument.',
            );
        }

        if (!in_array($targetEnvironment, EnvironmentType::ALL, true)) {
            throw new Exception(sprintf('Unknown environment "%s".', $targetEnvironment));
        }

        $this->environmentFileSetting->removeFilesForAllEnvironments();
        $this->environmentFileSetting->createFileForEnvironment($targetEnvironment);

        $output->writeln(sprintf('Application environment successfully changed to "%s".', $targetEnvironment));

        return Command::SUCCESS;
    }
}
