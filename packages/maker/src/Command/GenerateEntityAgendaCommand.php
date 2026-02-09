<?php

declare(strict_types=1);

namespace Shopsys\MakerBundle\Command;

use Exception;
use Override;
use RuntimeException;
use Shopsys\MakerBundle\EntityConfig\EntityConfigFactory;
use Shopsys\MakerBundle\Maker\BaseMaker;
use Shopsys\MakerBundle\Maker\DataFixtureMaker;
use Shopsys\MakerBundle\Maker\EntityMaker;
use Shopsys\MakerBundle\Maker\FacadeMaker;
use Shopsys\MakerBundle\Maker\NotFoundExceptionMaker;
use Shopsys\MakerBundle\Maker\RepositoryMaker;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'make:shopsys:entity-agenda',
    description: 'Generates entity, data object, data object factory, facade, repository, and data fixture classes for the given entity name',
)]
class GenerateEntityAgendaCommand extends Command
{
    public function __construct(protected readonly EntityConfigFactory $entityConfigFactory)
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function configure(): void
    {
        $this->addArgument(BaseMaker::ENTITY_NAME_ARGUMENT, InputArgument::REQUIRED, 'The entity name (e.g. <fg=yellow>Kitty</>)');
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $entityName = $input->getArgument(BaseMaker::ENTITY_NAME_ARGUMENT);

        $application = $this->getApplication();

        if ($application === null) {
            throw new RuntimeException('Application instance is not available');
        }

        $commandNames = [
            EntityMaker::getCommandName(),
            RepositoryMaker::getCommandName(),
            FacadeMaker::getCommandName(),
            NotFoundExceptionMaker::getCommandName(),
            DataFixtureMaker::getCommandName(),
        ];

        foreach ($commandNames as $commandName) {
            $commandInputParameters = [
                'command' => $commandName,
                BaseMaker::ENTITY_NAME_ARGUMENT => $entityName,
            ];

            $commandInput = new ArrayInput($commandInputParameters);

            try {
                $application->doRun($commandInput, $output);
            } catch (Exception $e) {
                $io->error(sprintf('Error running command %s: %s', $commandName, $e->getMessage()));
            }
        }

        return Command::SUCCESS;
    }
}
