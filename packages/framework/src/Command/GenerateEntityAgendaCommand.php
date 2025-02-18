<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Exception;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'shopsys:generate-entity-agenda',
    description: 'Generates entity, data object, data object factory, facade, repository, and data fixture classes for the given entity name',
)]
class GenerateEntityAgendaCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the entity to generate the agenda for');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $entityName = $input->getArgument('name');

        $application = $this->getApplication();

        if ($application === null) {
            throw new RuntimeException('Application instance is not available');
        }

        $commandNames = [
            'make:shopsys:repository',
            'make:shopsys:facade',
            'make:shopsys:not-found-exception',
            'make:shopsys:data-fixture',
        ];

        foreach ($commandNames as $commandName) {
            $commandInput = new ArrayInput([
                'command' => $commandName,
                'name' => $entityName,
            ]);

            try {
                $application->doRun($commandInput, $output);
            } catch (Exception $e) {
                $io->error(sprintf('Error running command %s: %s', $commandName, $e->getMessage()));
            }
        }

        return Command::SUCCESS;
    }
}
